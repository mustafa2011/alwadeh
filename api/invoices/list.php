<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';
use App\Core\Database;


try {


    $pdo = Database::getConnection();


    $stmt = $pdo->prepare(
    "
    SELECT

        i.id,
        i.invoice_number,
        i.invoice_kind,
        i.issue_date,
        i.invoice_status,

        z.clearance_status,
        z.reporting_status


    FROM invoices i


    LEFT JOIN invoice_zatca z

    ON z.invoice_id = i.id


    ORDER BY i.id DESC

    "
    );


    $stmt->execute();


    $rows = $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );



    foreach($rows as &$row){


        if(
            $row['clearance_status']
            === 'cleared'
        ){

            $row['zatca_status']
            =
            'Cleared';

        }

        elseif(
            $row['reporting_status']
            === 'reported'
        ){

            $row['zatca_status']
            =
            'Reported';

        }

        else {

            $row['zatca_status']
            =
            'Pending';

        }


    }



    echo json_encode([

        'success'=>true,

        'data'=>$rows

    ],
    JSON_UNESCAPED_UNICODE);



}

catch(Throwable $e){


    echo json_encode([

        'success'=>false,

        'message'=>$e->getMessage()

    ]);


}