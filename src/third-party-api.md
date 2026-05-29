# Third-Party API Reference

This document summarizes the GetStocks API integration for this project.

## Authentication

### POST /api/auth/login
URL: `https://getstocks.net/api/auth/login`

Request body:
- `email` (string, required)
- `password` (string, required)

Response:
- `status` (200)
- `result.access_token` (string)
- `result.token_type` (string, typically `bearer`)
- `result.expires_in` (numeric, often `0`)

Notes:
- The token does not expire by default.
- You can use `Authorization: Bearer YOUR_TOKEN` in headers.

## Profile

### GET /api/auth/profile
URL: `https://getstocks.net/api/auth/profile?token=YOUR-TOKEN-HERE`

Request:
- No body parameters required.

Response:
- `status` (200)
- `result.Profile` includes user fields such as `id`, `name`, `email`, `phone`, `created_at`, `updated_at`, etc.

Notes:
- Returns the authenticated user profile.

## Providers

### GET /api/v1/providers
URL: `https://getstocks.net/api/v1/providers?token=YOUR-TOKEN-HERE`

Request:
- No body parameters required.

Response:
- `status` (200)
- `result.norProvider` array
- `result.preProvider` array

Each provider item may include:
- `provName`
- `provSlug`
- `provHost`
- `provType`
- `provLogo`
- `provResolution`
- `provLicense`
- `provPrice`
- `provPriceBonus`
- `isPremium`
- `isAPIAccess`
- `status`
- `addedTime`

Notes:
- Use provider list to understand supported provider types for downloads.

## Orders

### GET /api/v1/orders
URL: `https://getstocks.net/api/v1/orders?token=YOUR-TOKEN-HERE`

Request:
- No body parameters required.

Response:
- `status` (200)
- `result.order` array

Each order item may include:
- `oID`
- `email`
- `oName`
- `oTransID`
- `oPayProvider`
- `oPackageID`
- `oTotalCost`
- `oVerified`
- `oComments`
- `status`
- `oCurrency`
- `oTimes`
- `oLastUpdate`
- `addedTime`

Notes:
- Returns invoice/order history.

## Balance

### GET /api/v1/balance
URL: `https://getstocks.net/api/v1/balance?token=YOUR-TOKEN-HERE`

Request:
- No body parameters required.

Response:
- `email`
- `bValue`
- `bBonus`
- `isSubscription`
- `bSubscriptionEnd`
- `bLastUpdate`
- `addedTime`

Notes:
- Returns current wallet balance and bonus information.

## Download Workflow

### Step 1: Get item information

### POST /api/v1/getinfo
URL: `https://getstocks.net/api/v1/getinfo?token=YOUR-TOKEN-HERE`

Request body:
- `link` (string, required)
- `ispre` (numeric, required) - `1` for premium, `0` for normal

Response:
- `status` (200)
- `result.itemSlug`
- `result.support`
- `result.id`
- `result.itemName`
- `result.itemType`
- `result.ispre`

Notes:
- This step is optional but useful to collect the correct supported `type` values.
- Use `result.support -> type` for the next step.

### Step 2: Start the download process

### POST /api/v1/getlink
URL: `https://getstocks.net/api/v1/getlink?token=YOUR-TOKEN-HERE`

Request body:
- `link` (string, required)
- `ispre` (numeric, required)
- `type` (string, optional)
- `webhook` (string, optional)

Response:
- `status` (200)
- `result.email`
- `result.provSlug`
- `result.itemID`
- `result.itemType`
- `result.isPremium`
- `title` (e.g. `Processing`)
- `message`

Notes:
- Starts the download flow.
- If `type` is not provided, default options may be used.
- The response usually indicates that the request is processing.

### Step 3: Check download status

### POST /api/v1/download-status
URL: `https://getstocks.net/api/v1/download-status?token=YOUR-TOKEN-HERE`

Request body:
- `slug` (string, required)
- `id` (string, required)
- `ispre` (numeric, required)
- `type` (string, required)

Response:
- `status` (200)
- `result.itemDCode` (string)
- `result.itemLink`
- `result.itemName`
- `result.itemFilename`
- `result.itemExt`
- `result.itemSize`
- `result.process`
- `result.status`
- `result.bValue`
- `result.bBonus`

Notes:
- Poll every 5-10 seconds until `status` becomes success (`1`) or fail.
- Do not call `download-status` immediately with `getlink`; wait until the download process stabilizes.
- If called too early, the download code may be invalid and return a 400.

### Final download link

Once `download-status` returns success and `itemDCode`, download from:

`https://getstocks.net/api/v1/download/ITEM-D-CODE?token=YOUR-TOKEN-HERE`

## Implementation notes

- Keep the access token safe.
- Prefer `Authorization: Bearer YOUR_TOKEN` if supporting header auth.
- Use `getinfo` to validate supported types before calling `getlink`.
- Use `download-status` polling to finalize the download.
