# ALWADEH - Progress Report
**Date:** 2026-07-19

---

# المرحلة الحالية

تم الانتهاء من مراجعة دورة الشهادات وربطها بالكامل بقاعدة البيانات، مع التخلص من الاعتماد على ملفات JSON الخاصة ببيانات الشركات والشهادات.

---

# ما تم إنجازه

## مراجعة Helpers

تمت مراجعة الملفات التالية:

- common_helper.php
- company_helper.php
- certificate_helper.php
- compliance_helper.php
- storage_helper.php
- file_helper.php
- config_helper.php
- invoice_helper.php

---

## إزالة الاعتماد على JSON

تم التأكد من إزالة الاعتماد على:

- company.json
- credentials.json
- certificate.json

من دورة الشهادات بالكامل.

---

## مصدر بيانات ZATCA

أصبحت جميع عمليات القراءة والكتابة تعتمد على:

- companies
- company_address
- company_tax_scheme
- company_legal_entity
- company_zatca_settings

بدلاً من ملفات JSON.

---

## دورة الشهادات

تمت مراجعة جميع المراحل:

- Generate CSR
- Compliance Certificate
- Compliance Checks
- Production Certificate
- Renewal

وجميعها أصبحت تعتمد على قاعدة البيانات.

---

## Current Company

تم التخلص من الاعتماد على:

```
Storage/current_company.json
```

واستبداله بقاعدة البيانات.

تم إنشاء جدول جديد:

```sql
user_current_company
```

لحفظ الشركة الحالية لكل مستخدم.

---

## تعديل setCurrentCompany()

أصبحت الدالة تقوم بـ:

- حفظ الشركة الحالية في Session.
- حفظ الشركة الحالية في جدول `user_current_company`.

---

## تعديل getCurrentCompany()

أصبحت الدالة تعمل بالتسلسل التالي:

1. قراءة الشركة من Session إذا كانت موجودة.
2. إذا لم توجد، يتم القراءة من جدول `user_current_company`.
3. يتم إعادة تخزينها داخل Session.
4. إرجاع رقم السجل التجاري (CRN).

وبذلك أصبحت الشركة الحالية محفوظة حتى بعد:

- Logout
- Login

دون الحاجة لأي ملف JSON.

---

## النتيجة

أصبحت قاعدة البيانات هي المصدر الوحيد لحفظ:

- بيانات الشركات
- إعدادات ZATCA
- الشهادات
- الشركة الحالية للمستخدم

ولم يعد المشروع يعتمد على أي ملفات JSON لإدارة هذه البيانات.

---

# الخطوة القادمة

## مراجعة Services بالكامل

البدء بمراجعة جميع ملفات Services للتأكد من عدم وجود أي اعتماد متبقٍ على ملفات JSON أو Storage فيما يخص البيانات، والتأكد أن جميع عمليات القراءة والكتابة تتم من قاعدة البيانات فقط، ثم البدء في مراجعة دورة إصدار الفواتير (Invoice Lifecycle) وربطها بالكامل بقاعدة البيانات.