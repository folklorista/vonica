<?php
header("Content-Type: text/html; charset=utf-8");

require './vendor/autoload.php';
use Tracy\Debugger;
use Slim\Slim;
use Nette\Neon;

Debugger::enable();

$config = \Nette\Neon\Neon::decode(file_get_contents('config/db.local.neon'));
$app = new Slim($config);

$dsn = "{$app->config('db.driver')}:dbname={$app->config('db.dbname')};charset=utf8";
$pdo = new PDO($dsn, $app->config('db.username'), $app->config('db.password'));
$db = new NotORM($pdo);

$app->get("/api/v1/seat", function () use ($app, $db) {
    $seats = array();
    foreach ($db->seat() as $seat) {
        $seats[]  = array(
            "id" => $seat['id'],
            "row" => $seat['row'],
            "col" => $seat['col'],
            "left" => $seat['css_left'],
            "top" => $seat['css_top'],
            "seat_group" => $seat->seat_group['name']
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $json = json_encode($seats);
    if (FALSE === $json) {
        echo json_last_error_msg();
    }
    echo $json;
});

$app->get("/api/v1/seat/reserved", function () use ($app, $db) {
    $reservations = array();
    foreach ($db->reservation_seat()
            ->where('reservation.confirmed = ?', true)
            ->or('DATE_SUB(NOW(), INTERVAL ? DAY) < reservation.created', 5)
            ->or('DATE_SUB(NOW(), INTERVAL ? DAY) < reservation.changed', 5)
            as $reservation) {
        $reservations[]  = array(
            "id" => $reservation->seat['id']
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $json = json_encode($reservations);
    if (FALSE === $json) {
        echo json_last_error_msg();
    }
    echo $json;
});

$app->post("/api/v1/reservation", function () use ($app, $db) {
  try {
    // get and decode JSON request body
    $request = $app->request();
    $body = $request->getBody();
    $data = json_decode($body);
    $inserted_row = $db->reservation->insert(array(
        'firstname' => $data->firstname,
        'lastname' => $data->lastname,
        'email' => $data->email,
        'phone' => $data->phone,
        'note' => $data->note,
        'created' => new \DateTime()
    ));

    foreach ($data->seats as $seat_id) {
        $db->reservation_seat->insert(array(
            'reservation_id' => $inserted_row['id'],
            'seat_id' => $seat_id,
            'created' => new \DateTime()
        ));
    }

    // return JSON-encoded response body
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode($data);
  } catch (Exception $e) {
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $e->getMessage());
  }
});


// run the Slim app
$app->run();