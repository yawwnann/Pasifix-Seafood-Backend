
## API Documentation

### Public Endpoints

-   `GET /api/kategori` — List all fish categories
-   `GET /api/ikan` — List all fish (with filters)
-   `GET /api/ikan/{ikan:slug}` — Get fish detail by slug
-   `POST /api/register` — Register a new user
-   `POST /api/login` — Login and get token
-   `POST /api/midtrans/notification` — Midtrans payment notification callback

### Authenticated Endpoints (require Bearer token)

-   `POST /api/logout` — Logout current user
-   `GET /api/user` — Get current user profile
-   `POST /api/user/profile-photo` — Update user profile photo

#### Pesanan (Orders)

-   `POST /api/pesanan` — Create a new order
-   `GET /api/pesanan` — List user orders
-   `GET /api/pesanan/{pesanan}` — Get order detail

#### Keranjang (Cart)

-   `GET /api/keranjang` — List cart items
-   `POST /api/keranjang` — Add item to cart
-   `PUT /api/keranjang/{keranjangItem}` — Update cart item quantity
-   `DELETE /api/keranjang/{keranjangItem}` — Remove item from cart

#### Payment

-   `POST /api/payment/initiate/{pesanan}` — Initiate payment for an order

---

All authenticated endpoints require the `Authorization: Bearer {token}` header. For more details on request/response formats, see the controller code or contact the backend team.
