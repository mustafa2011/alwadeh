# ALWADEH ZATCA - Session Summary

## Current Status

تم الانتقال من الاعتماد على JSON إلى قاعدة البيانات MySQL.

## Current Company Flow

- صفحة Companies:
  - إنشاء الشركة الأساسية.
  - اختيار Current Company.
  - لا تحتوي بيانات ZATCA الكاملة.

- صفحة Certificate Setup:
  - المصدر الرئيسي لبيانات إنشاء CSR.
  - تحتوي:
    - CRN
    - VAT
    - Organization Name
    - Branch Name
    - Address
    - Street
    - Building Number
    - Subdivision
    - City
    - Postal Zone
    - Business Category
    - Invoice Type

## Database Structure

الجداول المستخدمة:

- companies
- company_party
- company_address
- company_tax_scheme
- company_legal_entity

## Important Changes

تم فصل إنشاء الشركة عن بيانات الشهادة.

`syncCompanyToDatabase()`
مسؤولة عن إنشاء الشركة الأساسية فقط.

بيانات الشهادة يتم مزامنتها بواسطة:

`syncCertificateCompanyData($companyId, $data)`

## Current Company Loader

تم نقل:

`loadCurrentCompany()`

إلى helper.

وظيفتها:
- قراءة `$_SESSION['company_crn']`
- جلب الشركة من database.
- تحميل:
  - party
  - address
  - tax_scheme
  - legal_entity

## Current Fixes Done

- تم إصلاح مشكلة:
  - No current company selected.

- تم إصلاح:
  - party_name غير موجود.
  - استخدام الحقل الصحيح `name`.

- تم إصلاح:
  - Undefined crn.
  - استخدام `commercial_registration_number`.

- تم إصلاح:
  - updateCompanyStatus كان يتعامل مع status كـ array بينما الحقول أصبحت columns مستقلة.

## Current Database Notes

تم إنشاء CSR بنجاح.

تم إنشاء:
- certificate.csr
- private key

داخل Storage.

## Remaining Issue

التأكد من:
- عدم إنشاء سجل فارغ في company_address.
- إضافة unique key على company_id في company_address حتى يعمل ON DUPLICATE KEY.

## Next Step

البدء في:

`public function requestComplianceCertificate(string $otp): array`

داخل CertificateService.

المطلوب مراجعة:
- إرسال CSR إلى ZATCA.
- استخدام current company.
- قراءة certificate files.
- حفظ نتيجة Compliance Certificate.
- تحديث حالة الشركة.
tandalone'])
print('/mnt/data/ALWADEH_ZATCA_session_summary.md')