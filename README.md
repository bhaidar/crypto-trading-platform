# Crypto Trading Platform

This is a simple crypto trading platform built with Laravel and Vue.js. It allows users to place buy and sell orders for crypto assets, and it features a real-time order matching engine.

## Technologies Used

-   **Backend**: Laravel 12
-   **Frontend**: Vue.js 3 with Inertia.js
-   **Styling**: Tailwind CSS
-   **Build Tool**: Vite
-   **Real-time Events**: Pusher-js and Laravel Echo
-   **Database**: SQLite
-   **Testing**: PHPUnit

## Getting Started (How to Run Locally)

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/bhaidar/crypto-trading-platform.git
    cd crypto-trading-platform
    ```

2.  **Run the setup script:**
    This script will install all PHP and JS dependencies, create a `.env` file, generate an application key, run database migrations, and build the frontend assets.
    ```bash
    composer run-script setup
    ```

3.  **Run the development server:**
    This will start the Laravel development server, the queue listener, the log watcher, and the Vite server.
    ```bash
    composer run-script dev
    ```
    The application will be available at [http://localhost](http://localhost).

## How Trading Works

The core of the trading logic is handled by the `OrderService`. Here's a step-by-step explanation of the trading process:

1.  **User Places an Order:**
    -   A user decides to either buy or sell a specific quantity of a crypto asset at a certain price.
    -   When a "buy" order is placed, the system checks if the user has enough USD to cover the total cost of the order (`price * quantity`). If they do, that amount of their USD balance is "locked" to prevent it from being used in other trades.
    -   When a "sell" order is placed, the system checks if the user owns the asset and has a sufficient quantity. If they do, that quantity of the asset is "locked".

2.  **Order Matching:**
    -   After an order is placed, the system immediately tries to find a matching order from another user.
    -   For a **buy order**, the system looks for a **sell order** for the same asset where the sell price is less than or equal to the buy price.
    -   For a **sell order**, the system looks for a **buy order** for the same asset where the buy price is greater than or equal to the sell price.
    -   For a match to occur, the quantities of both orders must be identical.
    -   The system prioritizes the best price (lowest for sellers, highest for buyers) and then the earliest order if prices are equal.

3.  **Trade Execution:**
    -   If a matching order is found, a **trade** is executed.
    -   The trade is executed at the price of the *existing* order on the books (the `matchingOrder`), not necessarily the price of the newly placed order.
    -   A commission of 1.5% is charged to the buyer.
    -   Both the new order and the matching order are marked as `Filled`.

4.  **Balance Settlement:**
    -   The buyer's USD balance is debited for the total cost of the trade plus the commission. The purchased crypto asset is credited to their account.
    -   The seller's locked asset amount is debited, and the proceeds from the sale (in USD) are credited to their balance.
    -   An `OrderMatched` event is dispatched, which can be used to notify both users in real-time that their trade has been executed.

5.  **Unmatched Orders (Open Orders):**
    -   If no matching order is found immediately, the new order is placed in the **order book** and remains with an `Open` status.
    -   It will stay in the order book until a new order is placed that matches it, or until the user who placed it decides to cancel it.
    -   Cancelling an order will "release" the locked funds or assets, making them available to the user again.