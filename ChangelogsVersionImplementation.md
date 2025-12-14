# Changelog & Version Implementation History

This document records the summary of implemented features and modules for the NF-e Emitter project.

## [v0.10.0] - Fiscal: CC-e
**Timestamp:** 2025-12-14 01:30:00 -03:00
**Branch:** `dev/CCeImplementation`
**Commit:** `Feat: NFe CC-e Implementation`
**Changes:**
-   **Service**: Implemented `correction` method in `NFeService`.
-   **UI**: Correction Form (`nfe.correction`) and Link in Index.
-   **Verification**: `NFeCorrectionTest` (created and passed).
-   **Fixes**: Recreated missing `User` model; Fixed `CustomerFactory`, `AddressFactory` and `HasFactory` traits.

## [v0.9.0] - Fiscal: Cancellation
**Timestamp:** 2025-12-14 00:40:00 -03:00
**Branch:** `dev/cancellationImplementation`
**Commit:** `Feat: NFe Cancellation`
**Changes:**
-   **Service**: `cancel` method in `NFeService`.
-   **UI**: Cancellation Form.
-   **Verification**: `NFeCancellationTest`.

## [v0.8.0] - Fiscal: DANFE Generation
**Timestamp:** 2025-12-14 00:20:00 -03:00
**Branch:** `dev/danfeImplementation`
**Commit:** `Feat: DANFE PDF generation`
**Changes:**
-   **Deps**: Installed `nfephp-org/sped-da`.
-   **Service**: Implemented `DanfeService` (XML -> PDF).
-   **UI**: Added "Download/Visualizar PDF" buttons.
-   **Verification**: Verified with `DanfeTest`.

## [v0.7.0] - Fiscal: SEFAZ Integration
**Timestamp:** 2025-12-14 00:13:00 -03:00
**Branch:** `dev/sefazIntegration`
**Commit:** `Feat: SEFAZ transmission logic`
**Changes:**
-   **Database**: Added `protocolo`, `mensagem_sefaz`, `data_recebimento` to `nves` table.
-   **Service**: Implemented `NFeService::transmit` to send batch to SEFAZ and check status.
-   **UI**: Added "Transmitir" button to NFe list.
-   **Verification**: Verified with `NFeTransmissionTest` (mocked).

## [v0.6.0] - Fiscal: NFe Generation
**Timestamp:** 2025-12-14 00:07:00 -03:00
**Branch:** `dev/NFeImplementation`
**Commit:** `Feat: NFe generation logic`
**Changes:**
-   **Infrastructure**: Updated Dockerfile with `soap` extension (required for NFePHP).
-   **Dependencies**: Installed `nfephp-org/sped-nfe` via Composer.
-   **Database**: Created `nves` table to store emitted notes and XML paths.
-   **Service**: Implemented `NFeService` to construct and sign the NFe XML using the Company's certificate.
-   **Backend**: `NFeController` to handle the generation request.
-   **Frontend**: Created simplified UI (`nfe.create`) to select Emitter, Customer, and Products.
-   **Verification**: Added `NFeTest` to verify the generation and signing flow (mocked).

## [v0.5.0] - Fiscal: Certificate Management
**Timestamp:** 2025-12-13 23:53:00 -03:00
**Branch:** `dev/CertificateManagementImplementation`
**Commit:** `Feat: Certificate upload and encryption`
**Changes:**
-   **Database**: Created `certificates` table.
-   **Security**: Implemented encryption for certificate passwords using Laravel Casts (`encrypted`).
-   **Service**: `CertificateService` to handle .pfx upload, password validation, and Expiry Date extraction (using OpenSSL).
-   **UI**: Added "Upload Certificado" feature to the Company List.
-   **Verification**: Verified with `CertificateTest` (mocking file upload).

## [v0.4.0] - Core: Products Module
**Timestamp:** 2025-12-13 23:46:00 -03:00
**Branch:** `dev/productImplementation`
**Commit:** `Feat: Products module implementation`
**Changes:**
-   **Database**: Created `products` table with fiscal fields (`ncm`, `cest`, `origem`, `codigo_sku`).
-   **Backend**: Created `Product` model, `ProductController`, and `ProductService`.
-   **Frontend**: Implemented CRUD views (`index`, `create`) for Products.
-   **Verification**: Implemented `ProductTest` verifying creation and persistence.

## [v0.3.0] - Core: Customers Module
**Timestamp:** 2025-12-13 23:30:00 -03:00 (Approx)
**Branch:** `dev/feature/customers` (Merged)
**Changes:**
-   **Database**: Created `customers` table with validation fields (`cpf_cnpj`, `indicador_ie`).
-   **Backend**: Created `Customer` model and Controller.
-   **Frontend**: Implemented CRUD views for Customers.
-   **Verification**: Verified with Feature Tests.

## [v0.2.0] - Core: Companies Module
**Timestamp:** 2025-12-13 23:00:00 -03:00 (Approx)
**Branch:** `main` (Initial integration)
**Changes:**
-   **Database**: Created `companies` and `addresses` tables.
-   **Logic**: Implemented robust Service Layer pattern for Company creation (separating Address logic).
-   **Backend**: `CompanyController` and `Address` model.
-   **Verification**: Verified Database Transaction integrity in Service.

## [v0.1.0] - Environment & Infrastructure
**Timestamp:** 2025-12-13 22:00:00 -03:00 (Approx)
**Changes:**
-   **Docker**: Configured `docker-compose.dev.yml` for Nginx, PHP 8.2, and MySQL.
-   **Scripts**: Created `install_laravel.bat` for easy Windows setup.
-   **Laravel**: Initialized Laravel 11 skeleton.
-   **Configuration**: Set up `.env` and port mapping (Web: 8081).
