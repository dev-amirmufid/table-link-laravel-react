# API Specification

## Base URL

```
http://localhost:8080/api/v1
```

## Authentication

### Public Endpoints

The following endpoints do not require authentication:

- `GET /dashboard/analytics`
- `GET /transactions`
- `GET /transactions/{id}`
- `POST /auth/login`

### Protected Endpoints

The following endpoints require JWT authentication:

- `POST /auth/logout`
- `GET /auth/me`
- `POST /auth/refresh`

---

## Endpoints

### 1. Dashboard Analytics (GOD QUERY)

**Endpoint:** `GET /dashboard/analytics`

Returns all dashboard analytics data in a single request.

#### Query Parameters

| Parameter  | Type    | Required | Default | Description                                                                                                               |
| ---------- | ------- | -------- | ------- | ------------------------------------------------------------------------------------------------------------------------- |
| include    | string  | No       | all     | Comma-separated list: summary, trends, trending_items, user_classification, top_buyers, top_sellers, revenue_contribution |
| period     | string  | No       | daily   | Time grouping: daily, weekly, monthly                                                                                     |
| limit      | integer | No       | 10      | Number of items for top lists                                                                                             |
| start_date | string  | No       | -       | Start date (Y-m-d)                                                                                                        |
| end_date   | string  | No       | -       | End date (Y-m-d)                                                                                                          |
| user_type  | string  | No       | -       | Filter: domestic, foreign                                                                                                 |

#### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "summary": {
      "total_transactions": 300000,
      "total_quantity": 1500314908,
      "total_revenue": 9373374086319,
      "active_buyers": 20000,
      "active_sellers": 20000,
      "avg_transaction_price": 31244580
    },
    "trends": [
      {
        "period": "2025-11-25",
        "transactions": 138,
        "revenue": "4497693790"
      }
    ],
    "trending_items": [
      {
        "item_id": "uuid",
        "item_name": "Item Name",
        "total_revenue": 5000000000,
        "total_quantity": 50000
      }
    ],
    "user_classification": [
      {
        "user_type": "domestic",
        "count": 10000,
        "percentage": 50
      }
    ],
    "top_buyers": [
      {
        "user_id": "uuid",
        "user_name": "John Doe",
        "total_transactions": 50,
        "total_spent": 5000000000
      }
    ],
    "top_sellers": [
      {
        "user_id": "uuid",
        "user_name": "Jane Smith",
        "total_transactions": 45,
        "total_earned": 4500000000
      }
    ],
    "revenue_contribution": [
      {
        "item_id": "uuid",
        "item_name": "Item Name",
        "revenue": 5000000000,
        "percentage": 5.3
      }
    ]
  },
  "period": "daily",
  "limit": 10,
  "include": ["summary", "trends", ...],
  "cached": true
}
```

---

### 2. Clear Dashboard Cache

**Endpoint:** `POST /dashboard/cache/clear`

Clears the cached analytics data.

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Cache cleared successfully"
}
```

---

### 3. Get Transactions (Paginated)

**Endpoint:** `GET /transactions`

Returns paginated transaction list with optional filtering.

#### Query Parameters

| Parameter  | Type    | Required | Default | Description                    |
| ---------- | ------- | -------- | ------- | ------------------------------ |
| page       | integer | No       | 1       | Page number                    |
| per_page   | integer | No       | 15      | Items per page (max 100)       |
| search     | string  | No       | -       | Search in buyer/seller name    |
| start_date | string  | No       | -       | Start date (Y-m-d)             |
| end_date   | string  | No       | -       | End date (Y-m-d)               |
| user_type  | string  | No       | -       | Filter: domestic, foreign      |
| period     | string  | No       | daily   | Period: daily, weekly, monthly |

#### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "uuid",
        "buyer_id": "uuid",
        "seller_id": "uuid",
        "item_id": "uuid",
        "quantity": 100,
        "price": 50000,
        "created_at": "2025-11-25T00:00:00Z",
        "updated_at": null,
        "buyer": {
          "id": "uuid",
          "name": "John Doe",
          "email": "john@example.com",
          "type": "domestic"
        },
        "seller": {
          "id": "uuid",
          "name": "Jane Smith",
          "email": "jane@example.com",
          "type": "foreign"
        },
        "item": {
          "id": "uuid",
          "name": "Product Name"
        }
      }
    ],
    "first_page_url": "http://localhost:8080/api/v1/transactions?page=1",
    "from": 1,
    "last_page": 1000,
    "last_page_url": "http://localhost:8080/api/v1/transactions?page=1000",
    "links": [...],
    "next_page_url": "http://localhost:8080/api/v1/transactions?page=2",
    "path": "http://localhost:8080/api/v1/transactions",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 15000
  }
}
```

#### Error Response

```json
{
  "success": false,
  "message": "Failed to fetch transactions",
  "error": "Internal server error",
  "data": {
    "data": [],
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 0
  }
}
```

---

### 4. Get Single Transaction

**Endpoint:** `GET /transactions/{id}`

Returns a single transaction by UUID.

#### Path Parameters

| Parameter | Type          | Description      |
| --------- | ------------- | ---------------- |
| id        | string (UUID) | Transaction UUID |

#### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "buyer_id": "uuid",
    "seller_id": "uuid",
    "item_id": "uuid",
    "quantity": 100,
    "price": 50000,
    "created_at": "2025-11-25T00:00:00Z",
    "updated_at": null,
    "buyer": {...},
    "seller": {...},
    "item": {...}
  }
}
```

#### Error Response (404)

```json
{
  "success": false,
  "message": "Transaction not found"
}
```

---

### 5. Login

**Endpoint:** `POST /auth/login`

Authenticates user and returns JWT token.

#### Request Body

| Parameter | Type   | Required | Description   |
| --------- | ------ | -------- | ------------- |
| email     | string | Yes      | User email    |
| password  | string | Yes      | User password |

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": "uuid",
      "name": "Admin User",
      "email": "admin@example.com",
      "type": "domestic"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 20160
  }
}
```

#### Error Response (401)

```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

### 6. Logout

**Endpoint:** `POST /auth/logout`

Requires JWT authentication. Invalidates the current token.

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

---

### 7. Get Current User

**Endpoint:** `GET /auth/me`

Requires JWT authentication. Returns authenticated user info.

#### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "Admin User",
    "email": "admin@example.com",
    "type": "domestic"
  }
}
```

---

### 8. Refresh Token

**Endpoint:** `POST /auth/refresh`

Requires JWT authentication. Returns new JWT token.

#### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 20160
  }
}
```

---

## HTTP Status Codes

| Code | Description           |
| ---- | --------------------- |
| 200  | Success               |
| 201  | Created               |
| 400  | Bad Request           |
| 401  | Unauthorized          |
| 404  | Not Found             |
| 422  | Validation Error      |
| 500  | Internal Server Error |

---

## Error Response Format

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error (only in debug mode)",
  "data": {}
}
```
