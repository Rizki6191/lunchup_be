## register
    curl -i -X POST http://127.0.0.1:8000/api/auth/register   -H "Accept: application/json"   -H "Content-Type: application/json"   -d '{"username":"test100","email":"test100@example.com","password":"12345678","role":"jastiper"}'

    HTTP/1.1 201 Created
    Host: 127.0.0.1:8000
    Connection: close
    X-Powered-By: PHP/8.3.6
    Cache-Control: no-cache, private
    Date: Fri, 23 Jan 2026 11:22:59 GMT
    Content-Type: application/json
    Access-Control-Allow-Origin: *

    {"message":"Registration successful. Please login.","user":{"id":2,"username":"test100","email":"test100@example.com","role":"jastiper"}}

## login
    curl -i -X POST http://127.0.0.1:8000/api/auth/login   -H "Accept: application/json"   -H "Content-Type: application
    /json"   -d '{"email":"test100@example.com","password":"12345678"}'
    
    HTTP/1.1 200 OK
    Host: 127.0.0.1:8000
    Connection: close
    X-Powered-By: PHP/8.3.6
    Cache-Control: no-cache, private
    Date: Fri, 23 Jan 2026 11:24:13 GMT
    Content-Type: application/json
    Access-Control-Allow-Origin: *

    {"message":"Login successful","token":"1|2SkWWfilp26sfIS1LjA5qM9lMRs29kmK7lrA1Bfse82b9d4e","user":{"id":2,"username":"test100","email":"test100@example.com","role":"jastiper","created_at":"2026-01-23T11:22:59.000000Z"}}

## login (admin)
    curl -X POST http://127.0.0.1:8000/api/auth/login \
    -H "Content-Type: application/json" \
    -d '{
        "email": "testadmin@example.com",
        "password": "12345678"
    }'

    {"success":true,"message":"Login successful","token":"11|sewIuFiALodhsxn44kc2CsGunRoMkymB8AdyJXUMe704cfba","user":{"id":6,"username":"admin","email":"testadmin@example.com","role":"admin","created_at":"2026-01-23T14:35:52.000000Z"}}

### admin create product
    curl -X POST http://127.0.0.1:8000/api/admin/products \
    -H "Authorization: Bearer 11|sewIuFiALodhsxn44kc2CsGunRoMkymB8AdyJXUMe704cfba" \
    -H "Content-Type: application/json" \
    -d '{
        "name": "Nasi Goreng Spesial",
        "description": "Nasi goreng dengan ayam, udang, telur, dan sayuran",
        "price": 35000,
        "stock": 50,
        "category": "Makanan",
        "image_url": "https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=400&h=300&fit=crop"
    }'

    {"success":true,"data":{"name":"Nasi Goreng Spesial","description":"Nasi goreng dengan ayam, udang, telur, dan sayuran","price":35000,"stock":50,"category":"Makanan","image_url":"https:\/\/images.unsplash.com\/photo-1631452180519-c014fe946bc7?w=400&h=300&fit=crop","created_by":6,"updated_at":"2026-01-26T10:29:00.000000Z","created_at":"2026-01-26T10:29:00.000000Z","id":33},"message":"Product created successfully"}

## get all product/menu
     curl -X GET http://127.0.0.1:8000/api/products     -H "Authorization: Bearer 11|sewIuFiALodhsxn44kc2CsGunRoMkymB8AdyJXUMe704cfba"     -H
    "Content-Type: application/json"

    {"success":true,"data":{"current_page":1,"data":[{"id":33,"name":"Nasi Goreng Spesial","description":"Nasi goreng dengan ayam, udang, telur, dan sayuran","price":"35000.00","stock":50,"category":"Makanan","image_url":"https:\/\/images.unsplash.com\/photo-1631452180519-c014fe946bc7?w=400&h=300&fit=crop","created_by":6,"created_at":"2026-01-26T10:29:00.000000Z","updated_at":"2026-01-26T10:29:00.000000Z"}, ...

## get product/menu 1d
     curl -X GET http://127.0.0.1:8000/api/products/33     -H "Authorization: Bearer 11|sewIuFiALodhsxn44kc2CsGunRoMkymB8AdyJXUMe704cfba"
    -H "Content-Type: application/json"

    {"success":true,"data":{"id":33,"name":"Nasi Goreng Spesial","description":"Nasi goreng dengan ayam, udang, telur, dan sayuran","price":"35000.00","stock":50,"category":"Makanan","image_url":"https:\/\/images.unsplash.com\/photo-1631452180519-c014fe946bc7?w=400&h=300&fit=crop","created_by":6,"created_at":"2026-01-26T10:29:00.000000Z","updated_at":"2026-01-26T10:29:00.000000Z"},"message":"Product retrieved successfully"}

## get product/menu by category
     curl -X GET http://127.0.0.1:8000/api/products/category/makanan     -H "Authorization: Bearer 11|sewIuFiALodhsxn44kc2CsGunRoMkymB8AdyJXUMe704cfba"

    {"success":true,"data":{"current_page":1,"data":[],"first_page_url":"http:\/\/127.0.0.1:8000\/api\/products\/category\/makanan?page=1","from":null,"last_page":1,"last_page_url":"http:\/\/127.0.0.1:8000\/api\/products\/category\/makanan?page=1","links":[{"url":null,"label":"&laquo; Previous","page":null,"active":false},{"url":"http:\/\/127.0.0.1:8000\/api\/products\/category\/makanan?page=1","label":"1","page":1,"active":true},{"url":null,"label":"Next &raquo;","page":null,"active":false}],"next_page_url":null,"path":"http:\/\/127.0.0.1:8000\/api\/products\/category\/makanan","per_page":15,"prev_page_url":null,"to":null,"total":0},"message":"Products by category retrieved successfully"}

## get all cart
     curl -X GET http://127.0.0.1:8000/api/cart     -H "Authorization: Bearer 11|sewIuFiALodhsxn44kc2CsGunRoMkymB8AdyJXUMe704cfba"

    {"success":true,"data":{"items":[],"summary":{"items_count":0,"total_amount":0}},"message":"Cart retrieved successfully"}

*ON GOING*