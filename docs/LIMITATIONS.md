# Limitations

## Overview

This document outlines the current limitations of the system.

## Functional Limitations

### 1. Authentication

- **JWT Only:** No session-based authentication
- **No Registration:** Users cannot self-register
- **No Password Reset:** No password recovery mechanism
- **Single Role:** No role-based access control (all users are same type)

### 2. Analytics

- **Fixed Periods:** Only daily, weekly, monthly grouping
- **No Custom Ranges:** Cannot define custom time periods
- **Top N Only:** Can only see top 10/20/etc, not complete ranking
- **No Export:** No CSV/Excel export functionality

### 3. Transactions

- **No Create/Update:** Read-only transaction data
- **No Bulk Operations:** Cannot create multiple transactions
- **Limited Search:** Basic name search only
- **No Filters on List:** Only date and user_type filters

### 4. Data

- **No Soft Deletes:** Deleted data is permanently removed
- **No Audit Trail:** No history of changes
- **No Data Validation:** Minimal input validation

## Technical Limitations

### 1. Caching

- **Single Cache Store:** Uses MySQL, not Redis
- **5-Minute TTL:** Relatively long cache time
- **No Cache Tags:** Cannot selectively invalidate
- **Cache Stampede:** No protection against cache misses

### 2. Database

- **No Read Replicas:** Single MySQL instance
- **Limited Indexing:** Missing some potential indexes
- **No Query Optimization:** Raw SQL not always optimized
- **No Partitioning:** Large tables not partitioned

### 3. API

- **No Rate Limiting:** Vulnerable to abuse
- **No Versioning:** Single API version
- **No Pagination Metadata:** Limited pagination info
- **No Error Codes:** Only generic error messages

### 4. Frontend

- **No Offline Support:** Requires internet connection
- **No PWA:** Not a progressive web app
- **No Mobile App:** Web only
- **Limited Accessibility:** Some ARIA issues

### 5. Docker

- **No Horizontal Scaling:** Single container per service
- **No Health Checks:** Basic container health
- **No Log Aggregation:**分散 logging
- **No Monitoring:** No metrics collection

## Scalability Limitations

### Performance at Scale

| Operation      | Current | At Scale      |
| -------------- | ------- | ------------- |
| Full Analytics | ~2s     | May timeout   |
| Paginated List | ~100ms  | Will degrade  |
| Cache Hit      | ~20ms   | Acceptable    |
| Large Result   | N/A     | Not supported |

### Resource Constraints

- **Memory:** PHP-FPM limited to container memory
- **Connections:** MySQL connection pool not configured
- **Workers:** Limited PHP-FPM workers

## Future Improvements Needed

### High Priority

1. **Rate Limiting:** Protect API from abuse
2. **Read Replicas:** Distribute read load
3. **Redis Cache:** Faster cache storage
4. **Proper Indexing:** Add missing indexes

### Medium Priority

1. **JWT Refresh:** Token refresh mechanism
2. **Cursor Pagination:** For deep lists
3. **Real-time Updates:** WebSocket support
4. **Error Codes:** Structured error responses

### Low Priority

1. **Multi-timezone:** Timezone support
2. **Audit Logging:** Track changes
3. **Data Export:** CSV/Excel download
4. **PWA Features:** Offline support

## Known Issues

1. **Large Numbers:** Need formatCompactNumber for display
2. **Date Timezone:** UTC assumption may cause issues
3. **Cache Invalidation:** Manual clear needed
4. **Deep Pagination:** May be slow beyond page 100

## Deprecated Features

- None currently

## Unsupported Use Cases

1. **Multi-tenant:** Single application only
2. **Microservices:** Monolithic architecture
3. **Complex Workflows:** No workflow engine
4. **File Storage:** No file upload/download
