<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Services\Order\OrderService;
use App\Traits\ValidatesRequestData;
use App\CPU\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    use ValidatesRequestData;
    protected $orderService;
    protected $customerOrderRepository;

    public function __construct(OrderService $orderService, \App\Repositories\CustomerOrder\CustomerOrderInterface $customerOrderRepository)
    {
        $this->orderService = $orderService;
        $this->customerOrderRepository = $customerOrderRepository;
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $limit = $request->query('limit', Helpers::LIMIT_PER_PAGE);
        $search = $request->query('search', '');

        $orders = $this->orderService->paginate($limit, $search);

        return $this->successResponse($orders, 'Lấy danh sách đơn hàng thành công');
    }

    /**
     * Store a newly created order (user_id is always the authenticated user)
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Get authenticated user's id
            $userId = auth('sanctum')->id();

            if (!$userId) {
                return $this->errorResponse('Bạn chưa xác thực', Response::HTTP_UNAUTHORIZED);
            }

            // Create order with customer order relationship
            $order = $this->orderService->createWithCustomer($validatedData, $userId);

            return $this->successResponse($order, 'Tạo đơn hàng thành công', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Lỗi khi tạo đơn hàng');
        }
    }

    /**
     * Get authenticated user's orders
     */
    public function myOrders(Request $request)
    {
        try {
            $userId = auth('sanctum')->id();

            if (!$userId) {
                return $this->errorResponse('Bạn chưa xác thực', Response::HTTP_UNAUTHORIZED);
            }

            $limit = $request->query('limit', Helpers::LIMIT_PER_PAGE);
            $search = $request->query('search', '');

            // Get order IDs where user is owner using repository
            $customerOrders = $this->customerOrderRepository->get([],[] ,[
                'user_id' => $userId
            ]);
            
            $orderIds = $customerOrders->pluck('order_id')->toArray();

            // Build query
            $query = \App\Models\Order\Order::whereIn('id', $orderIds)
                ->orderBy('created_at', 'desc');

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_name', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%');
                });
            }

            $orders = $query->paginate($limit);

            return $this->successResponse($orders, 'Lấy danh sách đơn hàng của bạn thành công');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Lỗi khi lấy danh sách đơn hàng');
        }
    }

    /**
     * Display the specified order
     */
        public function show(Request $request, string $id)
    {
        $order = $this->orderService->find($id, ['users']);

        if (!$order) {
            return $this->errorResponse('Don hang khong ton tai', Response::HTTP_NOT_FOUND);
        }

        $rsaN = $request->header('X-Client-Rsa-N');
        $rsaE = $request->header('X-Client-Rsa-E');
        if (!is_string($rsaN) || $rsaN === '' || !is_string($rsaE) || $rsaE === '') {
            return $this->errorResponse('Missing RSA headers for secure masking', Response::HTTP_BAD_REQUEST);
        }

        $encryptedOrder = $this->maskOrderData($order->toArray(), $rsaN, $rsaE);
        return $this->successResponse($encryptedOrder, 'Lay thong tin don hang thanh cong');
    }

    public function decryptPayload(Request $request)
    {
        $payload = $request->validate([
            'encrypted_data' => 'required|array',
            'rsa' => 'required|array',
            'rsa.n' => 'required',
            'rsa.d' => 'required',
        ]);

        $maskingUrl = env('MASKING_SERVICE_URL', 'http://127.0.0.1:8080');

        try {
            $response = Http::timeout(5)->post($maskingUrl . '/decrypt', $payload);
            if ($response->successful()) {
                return $this->successResponse($response->json(), 'Giai ma du lieu thanh cong');
            }

            return $this->errorResponse('Giai ma that bai: ' . $response->body(), $response->status());
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Khong the ket noi toi decrypt service: ' . $e->getMessage(),
                Response::HTTP_BAD_GATEWAY
            );
        }
    }

    /**
     * Helper to mask order data using the C++ masking microservice
     */
    private function maskOrderData(array $orderData, ?string $rsaN = null, ?string $rsaE = null)
    {
        $maskingUrl = env('MASKING_SERVICE_URL', 'http://127.0.0.1:8080');

        try {
            $endpoint = '/secure-mask';
            $payload = [
                'data' => $orderData,
                'rsa' => [
                    'n' => $rsaN,
                    'e' => $rsaE,
                ],
            ];

            $response = Http::timeout(5)->post($maskingUrl . $endpoint, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            \Log::warning("Data masking service returned status {$response->status()} with body: {$response->body()}");
        } catch (\Exception $e) {
            \Log::warning("Failed to call data masking service: " . $e->getMessage());
        }

        // Fallback tạm thời đã được comment theo yêu cầu.
        // return $this->fallbackMaskOrderData($orderData);
        return $orderData;
    }

    // private function fallbackMaskOrderData(array $orderData): array
    // {
    //     if (!empty($orderData['receiver_name']) && is_string($orderData['receiver_name'])) {
    //         $orderData['receiver_name'] = $this->fallbackMaskName($orderData['receiver_name']);
    //     }

    //     if (!empty($orderData['receiver_phone']) && is_string($orderData['receiver_phone'])) {
    //         $orderData['receiver_phone'] = $this->fallbackMaskPhone($orderData['receiver_phone']);
    //     }

    //     if (!empty($orderData['receiver_email']) && is_string($orderData['receiver_email'])) {
    //         $orderData['receiver_email'] = $this->fallbackMaskEmail($orderData['receiver_email']);
    //     }

    //     if (!empty($orderData['shipping_address']) && is_string($orderData['shipping_address'])) {
    //         $orderData['shipping_address'] = $this->fallbackMaskAddress($orderData['shipping_address']);
    //     }
    //     if (!empty($orderData['notes']) && is_string($orderData['notes'])) {
    //         $orderData['notes'] = $this->fallbackMaskNote($orderData['notes']);
    //     }

    //     return $orderData;
    // }

    // private function fallbackMaskName(string $value): string
    // {
    //     $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);

    //     if (!$chars || count($chars) === 0) {
    //         return '***';
    //     }

    //     if (count($chars) <= 4) {
    //         return $chars[0] . '***';
    //     }

    //     return implode(', array_slice($chars, 0, 2)) . '***' . implode(', array_slice($chars, -2));
    // }

    // private function fallbackMaskPhone(string $value): string
    // {
    //     $digits = preg_replace('/[^\d+]/', ', $value) ?? ';

    //     if (strlen($digits) <= 6) {
    //         return '***';
    //     }

    //     return substr($digits, 0, 3) . '****' . substr($digits, -3);
    // }

    // private function fallbackMaskEmail(string $value): string
    // {
    //     $parts = explode('@', $value, 2);

    //     if (count($parts) !== 2) {
    //         return '***';
    //     }

    //     [$localPart, $domain] = $parts;
    //     $chars = preg_split('//u', $localPart, -1, PREG_SPLIT_NO_EMPTY);

    //     if (!$chars || count($chars) === 0) {
    //         return '****@' . $domain;
    //     }

    //     if (count($chars) <= 3) {
    //         return $chars[0] . '****@' . $domain;
    //     }

    //     return implode(', array_slice($chars, 0, 3)) . '****@' . $domain;
    // }

    // private function fallbackMaskAddress(string $value): string
    // {
    //     $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);

    //     if (!$chars || count($chars) === 0) {
    //         return '***';
    //     }

    //     if (count($chars) <= 10) {
    //         return implode(', array_slice($chars, 0, 3)) . '****';
    //     }

    //     return implode(', array_slice($chars, 0, 5)) . '****' . implode(', array_slice($chars, -8));
    // }

    // private function fallbackMaskNote(string $value): string
    // {
    //     $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);

    //     if (!$chars || count($chars) === 0) {
    //         return '***';
    //     }

    //     if (count($chars) <= 8) {
    //         return implode(', array_slice($chars, 0, 2)) . '***';
    //     }

    //     return implode(', array_slice($chars, 0, 4)) . '***' . implode(', array_slice($chars, -4));
    // }

    /**
     * Update the specified order
     * User can only update their own orders, Super Admin can update any order
     */
    public function update(UpdateOrderRequest $request, string $id)
    {
        try {
            $userId = auth('sanctum')->id();
            $user = auth('sanctum')->user();

            if (!$userId || !$user) {
                return $this->errorResponse('Bạn chưa xác thực', Response::HTTP_UNAUTHORIZED);
            }

            $order = $this->orderService->find($id);

            if (!$order) {
                return $this->errorResponse('Đơn hàng không tồn tại', Response::HTTP_NOT_FOUND);
            }

            // Check if user owns this order (unless they are super admin)
            if (!$user->is_super_admin) {
                $customerOrder = $this->customerOrderRepository->first([
                    'order_id' => $id,
                    'user_id' => $userId
                ], []);

                if (!$customerOrder) {
                    return $this->errorResponse('Bạn không có quyền sửa đơn hàng này', Response::HTTP_FORBIDDEN);
                }
            }

            $updatedOrder = $this->orderService->update($order, $request->validated());

            return $this->successResponse($updatedOrder, 'Cập nhật đơn hàng thành công');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Lỗi khi cập nhật đơn hàng');
        }
    }

    /**
     * Remove the specified order
     */
    public function destroy(string $id)
    {
        try {
            $order = $this->orderService->find($id);

            if (!$order) {
                return $this->errorResponse('Đơn hàng không tồn tại', Response::HTTP_NOT_FOUND);
            }

            $this->orderService->delete($order);

            return $this->successResponse(null, 'Xóa đơn hàng thành công');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Lỗi khi xóa đơn hàng');
        }
    }

    /**
     * Update order status (Super Admin only)
     */
    public function updateStatus(UpdateOrderStatusRequest $request, string $id)
    {
        try {
            $order = $this->orderService->find($id);

            if (!$order) {
                return $this->errorResponse('Đơn hàng không tồn tại', Response::HTTP_NOT_FOUND);
            }

            $updatedOrder = $this->orderService->update($order, $request->validated());

            return $this->successResponse($updatedOrder, 'Cập nhật trạng thái đơn hàng thành công');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Lỗi khi cập nhật trạng thái đơn hàng');
        }
    }
}


