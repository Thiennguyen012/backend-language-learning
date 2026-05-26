<?php

namespace App\Providers;

use App\Repositories\User\UserInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\Order\OrderInterface;
use App\Repositories\Order\OrderRepository;
use App\Repositories\CustomerOrder\CustomerOrderInterface;
use App\Repositories\CustomerOrder\CustomerOrderRepository;
use App\Repositories\Flashcard\FlashcardInterface;
use App\Repositories\Flashcard\FlashcardRepository;
use App\Repositories\FlashcardCollection\FlashcardCollectionInterface;
use App\Repositories\FlashcardCollection\FlashcardCollectionRepository;
use App\Repositories\TestType\TestTypeInterface;
use App\Repositories\TestType\TestTypeRepository;
use App\Repositories\Question\QuestionInterface;
use App\Repositories\Question\QuestionRepository;
use App\Services\Order\OrderService;
use App\Services\CustomerOrderService\CustomerOrderService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(OrderInterface::class, OrderRepository::class);
        $this->app->bind(CustomerOrderInterface::class, CustomerOrderRepository::class);
        $this->app->bind(FlashcardInterface::class, FlashcardRepository::class);
        $this->app->bind(FlashcardCollectionInterface::class, FlashcardCollectionRepository::class);
        $this->app->bind(TestTypeInterface::class, TestTypeRepository::class);
        $this->app->bind(QuestionInterface::class, QuestionRepository::class);

        // Service bindings
        $this->app->bind(CustomerOrderService::class, function ($app) {
            return new CustomerOrderService($app->make(CustomerOrderInterface::class));
        });

        $this->app->bind(OrderService::class, function ($app) {
            return new OrderService(
                $app->make(OrderInterface::class),
                $app->make(CustomerOrderService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
