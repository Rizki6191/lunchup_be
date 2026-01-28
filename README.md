📦 API Documentation — Auth, Product, Cart

Base URL

http://127.0.0.1:8000/api


Authorization

Authorization: Bearer {token}

🔐 AUTHENTICATION
1️⃣ Register User

POST /auth/register

Request

curl -X POST http://127.0.0.1:8000/api/auth/register \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-d '{
  "username": "test100",
  "email": "test100@example.com",
  "password": "12345678",
  "role": "jastiper"
}'


Response — 201

{
  "message": "Registration successful. Please login.",
  "user": {
    "id": 2,
    "username": "test100",
    "email": "test100@example.com",
    "role": "jastiper"
  }
}

2️⃣ Login User

POST /auth/login

Request

curl -X POST http://127.0.0.1:8000/api/auth/login \
-H "Content-Type: application/json" \
-d '{
  "email": "test100@example.com",
  "password": "12345678"
}'


Response — 200

{
  "message": "Login successful",
  "token": "1|xxxxxxxxxxxxxxxx",
  "user": {
    "id": 2,
    "username": "test100",
    "email": "test100@example.com",
    "role": "jastiper",
    "created_at": "2026-01-23T11:22:59.000000Z"
  }
}

3️⃣ Login Admin

POST /auth/login

Request

curl -X POST http://127.0.0.1:8000/api/auth/login \
-H "Content-Type: application/json" \
-d '{
  "email": "testadmin@example.com",
  "password": "12345678"
}'


Response

{
  "success": true,
  "message": "Login successful",
  "token": "11|xxxxxxxxxxxxxxxx",
  "user": {
    "id": 6,
    "username": "admin",
    "email": "testadmin@example.com",
    "role": "admin"
  }
}

🍔 PRODUCT / MENU
4️⃣ Admin Create Product

POST /admin/products
🔒 Admin only

Request

curl -X POST http://127.0.0.1:8000/api/admin/products \
-H "Authorization: Bearer {ADMIN_TOKEN}" \
-H "Content-Type: application/json" \
-d '{
  "name": "Nasi Goreng Spesial",
  "description": "Nasi goreng dengan ayam, udang, telur, dan sayuran",
  "price": 35000,
  "stock": 50,
  "category": "Makanan",
  "image_url": "https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=400&h=300&fit=crop"
}'


Response

{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 33,
    "name": "Nasi Goreng Spesial",
    "price": 35000,
    "stock": 50,
    "category": "Makanan",
    "created_by": 6
  }
}

5️⃣ Get All Products (Pagination)

GET /products

Request

curl -X GET http://127.0.0.1:8000/api/products \
-H "Authorization: Bearer {TOKEN}"


Response

{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [ { "id": 33, "name": "Nasi Goreng Spesial" } ],
    "last_page": 3,
    "per_page": 15,
    "total": 33
  },
  "message": "Products retrieved successfully"
}


📌 Produk ada di: data.data

6️⃣ Get Product by ID

GET /products/{id}

Request

curl -X GET http://127.0.0.1:8000/api/products/33 \
-H "Authorization: Bearer {TOKEN}"


Response

{
  "success": true,
  "data": {
    "id": 33,
    "name": "Nasi Goreng Spesial",
    "price": "35000.00",
    "stock": 50
  },
  "message": "Product retrieved successfully"
}

7️⃣ Get Products by Category

GET /products/category/{category}

Request

curl -X GET http://127.0.0.1:8000/api/products/category/makanan \
-H "Authorization: Bearer {TOKEN}"


Response

{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [],
    "total": 0
  },
  "message": "Products by category retrieved successfully"
}


⚠️ Catatan penting

Category case-sensitive

Data disimpan "Makanan" tapi dipanggil "makanan"

Solusi backend:

whereRaw('LOWER(category) = ?', [strtolower($category)])

🛒 CART
8️⃣ Get Cart

GET /cart

Request

curl -X GET http://127.0.0.1:8000/api/cart \
-H "Authorization: Bearer {TOKEN}"


Response

{
  "success": true,
  "data": {
    "items": [],
    "summary": {
      "items_count": 0,
      "total_amount": 0
    }
  },
  "message": "Cart retrieved successfully"
}


curl -X PUT http://127.0.0.1:8000/api/admin/products/32 \
-H "Authorization: Bearer 11|sewIuFiALodhsxn44kc2CsGunRoMkymB8AdyJXUMe704cfba" \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-d '{
  "name": "Soda Gembira Jumbo",
  "description": "Minuman soda dengan sirup dan susu ukuran jumbo",
  "price": 22000,
  "stock": 25,
  "category": "Minuman",
  "image_url": "https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=400&h=300&fit=crop"
}'

curl -X DELETE http://127.0.0.1:8000/api/admin/products/33 -H "Authorization: Bearer 11|sewIuFiALodhsxn44kc2CsGunRoMkymB8AdyJXUMe704cfba"