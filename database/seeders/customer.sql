SET FOREIGN_KEY_CHECKS=0;

INSERT INTO customers
(
    id,
    company_id,
    customer_type,
    customer_name,
    created_at,
    updated_at
)
VALUES
(
    6,
    101,
    'company',
    'Test Customer Company',
    NOW(),
    NOW()
);

INSERT INTO customer_party
(
    customer_id,
    endpoint_id,
    party_name,
    endpoint_scheme
)
VALUES
(
    6,
    '300000000000003',
    'Test Customer Company',
    'CRN'
);

INSERT INTO customer_address
(
    customer_id,
    street_name,
    building_number,
    plot_identification,
    city_name,
    postal_zone,
    country_code
)
VALUES
(
    6,
    'King Fahd Road',
    '1234',
    'Al Olaya',
    'Riyadh',
    '12211',
    'SA'
);

INSERT INTO customer_tax_scheme
(
    customer_id
)
VALUES
(
    6
);

INSERT INTO customer_legal_entity
(
    customer_id,
    company_id_value,
    registration_name
)
VALUES
(
    5,
    101,
    'Test Customer Company'
);

SET FOREIGN_KEY_CHECKS=1;