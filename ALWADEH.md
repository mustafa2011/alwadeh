# ALWADEH ZATCA
## Project Status
Last Updated: 2026-07-19

---

# Project Overview

ALWADEH is a PHP-based ERP and ZATCA e-Invoicing system built around the Saleh7/php-zatca library.

The project follows a layered architecture:

- Core
- Controllers
- Services
- Helpers
- Database
- Storage
- Pages
- API

The long-term goal is to complete the full Phase 2 ZATCA invoice lifecycle while keeping all business logic centralized inside the Services layer.

---

# Current Architecture

```
Browser
    │
    ▼
Pages
    │
    ▼
Router
    │
    ▼
Controllers
    │
    ▼
Services
    │
    ├── Saleh7
    ├── Database
    └── Storage
    │
    ▼
Response
```

---

# Current Database Model

The database has been redesigned to follow the UBL structure instead of storing duplicated data.

## Company

```
companies
    │
    ├── company_party
    ├── company_address
    ├── company_tax_scheme
    └── company_legal_entity
```

## Customer

```
customers
    │
    ├── customer_party
    ├── customer_address
    ├── customer_tax_scheme
    └── customer_legal_entity
```

Invoice snapshots will store customer information independently to preserve historical data.

---

# Certificate Workflow

Current workflow:

```
Create Company
        │
        ▼
Save Database
        │
        ▼
Select Current Company
        │
        ▼
Certificate Wizard
        │
        ├── Generate CSR
        ├── Compliance CSID
        └── Production CSID
```

Status:

- Companies ✔
- Company Database ✔
- Current Company ✔
- CSR ✔
- Compliance Certificate ✔
- Production Certificate ✔
- Certificate Storage ✔

---

# Invoice Workflow (Target)

```
Create Invoice
        │
        ▼
Invoice Builder
        │
        ▼
UBL Objects
        │
        ▼
Saleh7 Mapping
        │
        ▼
XML Generation
        │
        ▼
Digital Signature
        │
        ▼
Invoice Hash
        │
        ▼
QR Code
        │
        ▼
Reporting / Clearance
        │
        ▼
Store XML
        │
        ▼
Generate PDF
```

---

# Project Progress

## Completed

- Authentication
- Session Management
- Routing
- Bootstrap
- Database Layer
- Companies Module
- Company UBL Structure
- Customer UBL Structure
- Certificate Wizard
- CSR Generation
- Compliance CSID
- Production CSID
- Storage Structure
- Saleh7 Certificate Integration

---

## In Progress

- Invoice Module

---

## Not Started

- Complete Invoice Builder
- UBL Invoice Mapping
- XML Generation
- XML Validation
- Digital Signature
- Invoice Hash
- QR Builder
- Reporting API
- Clearance API
- Dashboard
- Reports
- Notifications
- Final Testing
- Production Release

---

# Services Status

| Service | Status |
|----------|--------|
| CompanyService | ✔ |
| CertificateService | ✔ |
| CSRService | ✔ |
| ComplianceService | ✔ |
| ProductionService | ✔ |
| InvoiceService | 🚧 |
| XMLService | ⏳ |
| QRService | ⏳ |

---

# Storage Structure

```
Storage/

Companies/
Certificates/
CSR/
XML/
SignedInvoices/
QR/
Logs/
PDF/
```

---

# Technical Notes

- Database is now the primary data source.
- Remaining JSON-based logic should be eliminated.
- Business logic should reside in Services.
- Helpers should contain only reusable utility functions.
- Saleh7 remains the single source for ZATCA implementation.
- Maintain compatibility with UBL and MySQL 8.

---

# Development Roadmap

## Phase 1

Project Analysis

Status:

Completed ✔

---

## Phase 2

Certificate Lifecycle

Status:

Completed ✔

---

## Phase 3

Invoice Lifecycle

Status:

Next Milestone 🚧

Tasks:

- Build InvoiceService
- Build UBL Objects
- Map Invoice to Saleh7
- Generate XML
- Validate XML
- Sign XML
- Generate Hash
- Generate QR
- Submit Reporting
- Submit Clearance
- Store Documents

---

## Phase 4

Dashboard

Pending

---

## Phase 5

Reports

Pending

---

## Phase 6

Testing

Pending

---

## Phase 7

Production Release

Pending

---

# Next Development Task

Start implementing the complete invoice lifecycle using the existing architecture and the Saleh7 library.

Priority:

1. InvoiceService
2. Invoice UBL Builder
3. XML Generation
4. XML Validation
5. XML Signature
6. QR
7. Reporting
8. Clearance
9. PDF
10. Dashboard Integration

---

# General Rules

- Follow the Saleh7 library.
- Do not duplicate ZATCA logic.
- Keep business logic inside Services.
- Use Database as the single source of truth.
- Maintain UBL compatibility.
- Preserve the current project architecture.
```