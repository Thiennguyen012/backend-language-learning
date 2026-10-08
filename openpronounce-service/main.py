import os
import tempfile
from pathlib import Path

from fastapi import FastAPI, File, Form, HTTPException, UploadFile
from openpronounce import load_audio, compare_audio_with_text

app = FastAPI(title="OpenPronounce service")


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
