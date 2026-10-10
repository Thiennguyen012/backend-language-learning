import os
import json
import math
import tempfile
import urllib.error
import urllib.request
from pathlib import Path

from fastapi import FastAPI, File, Form, HTTPException, UploadFile
from faster_whisper import WhisperModel
from openpronounce import load_audio, compare_audio_with_text

app = FastAPI(title="OpenPronounce service")
_whisper_model = None


def whisper_model() -> WhisperModel:
    global _whisper_model
    if _whisper_model is None:
        _whisper_model = WhisperModel(
            os.getenv("SPEAKING_LEVEL_ASR_MODEL", "small.en"),
            device=os.getenv("WHISPER_DEVICE", "cpu"),
            compute_type=os.getenv("WHISPER_COMPUTE_TYPE", "int8"),
            download_root=os.getenv("HF_HOME", "/models"),
        )
    return _whisper_model


def transcribe_audio(audio_path: str) -> tuple[str, float, float, float]:
    segments, info = whisper_model().transcribe(
        audio_path,
        language="en",
        beam_size=5,
        vad_filter=True,
        word_timestamps=True,
    )
    segment_list = list(segments)
    transcript = " ".join(segment.text.strip() for segment in segment_list).strip()
    speech_seconds = sum(max(0.0, segment.end - segment.start) for segment in segment_list)
    duration = float(getattr(info, "duration", 0.0) or 0.0)
    if segment_list:
        weighted_log_probability = sum(
            float(segment.avg_logprob) * max(0.1, segment.end - segment.start)
            for segment in segment_list
        ) / sum(max(0.1, segment.end - segment.start) for segment in segment_list)
        average_no_speech_probability = sum(
            float(segment.no_speech_prob) for segment in segment_list
        ) / len(segment_list)
        recognition_confidence = math.exp(min(0.0, weighted_log_probability))
        pronunciation_score = recognition_confidence * (1.0 - average_no_speech_probability * 0.5) * 100
    else:
        pronunciation_score = 0.0
    return transcript, duration, speech_seconds, round(max(0.0, min(100.0, pronunciation_score)), 1)


def llm_assessment(question: str, topic: str, transcript: str) -> dict:
    prompt = f"""You are an English speaking examiner. Evaluate the answer using CEFR principles.
Question topic: {topic}
Question: {question}
Learner transcript: {transcript}

Return JSON only with this exact shape:
{{
  "grammar_score": 0,
  "vocabulary_score": 0,
  "coherence_score": 0,
  "topic_relevance_score": 0,
  "grammar_errors": [{{"original": "", "corrected": "", "explanation": ""}}],
  "strengths": [""],
  "improvements": [""],
  "upgraded_answer": ""
}}
All scores must be integers from 0 to 100. Keep feedback concise and concrete."""
    payload = json.dumps({
        "model": os.getenv("OLLAMA_MODEL", "qwen2.5:3b"),
        "stream": False,
        "format": "json",
        "messages": [{"role": "user", "content": prompt}],
        "options": {"temperature": 0.1},
    }).encode("utf-8")
    request = urllib.request.Request(
        os.getenv("OLLAMA_URL", "http://ollama:11434").rstrip("/") + "/api/chat",
        data=payload,
        headers={"Content-Type": "application/json"},
        method="POST",
    )
    try:
        with urllib.request.urlopen(request, timeout=180) as response:
            outer = json.loads(response.read().decode("utf-8"))
        result = json.loads(outer["message"]["content"])
    except (urllib.error.URLError, TimeoutError, KeyError, ValueError, json.JSONDecodeError) as exception:
        raise RuntimeError("The local language evaluator is unavailable or returned invalid JSON.") from exception

    for key in ("grammar_score", "vocabulary_score", "coherence_score", "topic_relevance_score"):
        result[key] = max(0, min(100, int(result.get(key, 0))))
    for key in ("grammar_errors", "strengths", "improvements"):
        if not isinstance(result.get(key), list):
            result[key] = []
    result["upgraded_answer"] = str(result.get("upgraded_answer", ""))
    return result


def cefr_level(score: float, word_count: int) -> str:
    if score >= 90 and word_count >= 180:
        return "C2"
    if score >= 78 and word_count >= 130:
        return "C1"
    if score >= 65 and word_count >= 90:
        return "B2"
    if score >= 50 and word_count >= 55:
        return "B1"
    if score >= 35 and word_count >= 25:
        return "A2"
    return "A1"


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok"}


@app.post("/pronunciation")
def pronunciation(
    file: UploadFile = File(...),
    expected_text: str = Form(...),
    lang: str = Form("en"),
) -> dict:
    if lang not in {"en", "fr", "es", "de", "it", "pt", "nl"}:
        raise HTTPException(status_code=422, detail="Unsupported OpenPronounce language.")
    if not expected_text.strip():
        raise HTTPException(status_code=422, detail="expected_text is required.")

    suffix = Path(file.filename or "audio.wav").suffix or ".wav"
    try:
        with tempfile.NamedTemporaryFile(suffix=suffix, delete=False) as temporary:
            temporary.write(file.file.read())
            audio_path = temporary.name

        audio = load_audio(audio_path)
        result = compare_audio_with_text(audio, expected_text.strip(), lang=lang)
        return result
    except Exception as exception:
        raise HTTPException(status_code=500, detail="Pronunciation scoring failed.") from exception
    finally:
        if "audio_path" in locals():
            os.unlink(audio_path)


@app.post("/speaking-level")
def speaking_level(
    file: UploadFile = File(...),
    question: str = Form(...),
    topic: str = Form(...),
    lang: str = Form("en"),
) -> dict:
    if lang != "en":
        raise HTTPException(status_code=422, detail="Speaking level assessment currently supports English only.")
    if not question.strip() or not topic.strip():
        raise HTTPException(status_code=422, detail="Question and topic are required.")

    suffix = Path(file.filename or "audio.m4a").suffix or ".m4a"
    try:
        with tempfile.NamedTemporaryFile(suffix=suffix, delete=False) as temporary:
            temporary.write(file.file.read())
            audio_path = temporary.name

        transcript, duration, speech_seconds, pronunciation_score = transcribe_audio(audio_path)
        if len(transcript.split()) < 2:
            raise HTTPException(status_code=422, detail="No clear English speech was detected. Please record again.")

        word_count = len(transcript.split())
        words_per_minute = word_count / max(speech_seconds, 1.0) * 60
        pace_score = max(0.0, 100.0 - abs(words_per_minute - 125.0) * 0.8)
        speech_ratio = min(1.0, speech_seconds / max(duration, 1.0))
        fluency_score = round(pace_score * 0.7 + speech_ratio * 100 * 0.3, 1)

        language = llm_assessment(question, topic, transcript)
        scores = {
            "pronunciation": round(pronunciation_score, 1),
            "fluency": fluency_score,
            "grammar": language["grammar_score"],
            "vocabulary": language["vocabulary_score"],
            "coherence": language["coherence_score"],
            "topic_relevance": language["topic_relevance_score"],
        }
        overall = round(
            scores["pronunciation"] * 0.20
            + scores["fluency"] * 0.20
            + scores["grammar"] * 0.20
            + scores["vocabulary"] * 0.15
            + scores["coherence"] * 0.15
            + scores["topic_relevance"] * 0.10,
            1,
        )

        return {
            "transcript": transcript,
            "overall_score": overall,
            "cefr_level": cefr_level(overall, word_count),
            "scores": scores,
            "grammar_errors": language["grammar_errors"],
            "strengths": language["strengths"],
            "improvements": language["improvements"],
            "upgraded_answer": language["upgraded_answer"],
            "metrics": {
                "duration_seconds": round(duration, 1),
                "speech_seconds": round(speech_seconds, 1),
                "word_count": word_count,
                "words_per_minute": round(words_per_minute, 1),
                "pronunciation_method": "whisper_recognition_confidence",
            },
        }
    except HTTPException:
        raise
    except Exception as exception:
        raise HTTPException(status_code=500, detail=str(exception)) from exception
    finally:
        if "audio_path" in locals():
            os.unlink(audio_path)
