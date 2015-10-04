<?php
header("Content-Type: text/html; charset=utf-8");

require './vendor/autoload.php';
use Tracy\Debugger;
use Slim\Slim;
use Nette\Neon;

Debugger::enable();

// slim
$config = \Nette\Neon\Neon::decode(file_get_contents('config/db.local.neon'));
$app = new Slim($config);

// database
$dsn = "{$app->config('db.driver')}:host={$app->config('db.hostname')};dbname={$app->config('db.dbname')};charset=utf8";
$pdo = new PDO($dsn, $app->config('db.username'), $app->config('db.password'));
$db = new NotORM($pdo);

// mailer
$transport = Swift_MailTransport::newInstance();
$mailer = Swift_Mailer::newInstance($transport);

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
            ->where('reservation.cancelled IS NULL')
            as $reservation) {
        $reservations[]  = array(
            "id" => $reservation->seat['id'],
            "type" => $reservation->reservation['type']
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $json = json_encode($reservations);
    if (FALSE === $json) {
        echo json_last_error_msg();
    }
    echo $json;
});

$app->post("/api/v1/reservation", function () use ($app, $db, $mailer) {
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

		$seats = $db->reservation_seat()
		 ->select('seat.row, seat.col, seat.seat_group.name')
     ->where("reservation_id = ?", $inserted_row['id']);
    $reservationEmail = $app->view->fetch('email/reservation.php', array(
				'seats' => $seats,
				'price_per_ticket' => 150
			)
		);

    // Setting all needed info and passing in my email template.
    $message = Swift_Message::newInstance('Potvrzení rezervace vstupenek')
                    ->setFrom(array('vonica@vonica.cz' => 'Vonica Zlín'))
                    ->setTo(array($data->email => $data->firstname . ' ' . $data->lastname))
                    ->setBody($reservationEmail)
                    ->setContentType("text/html");

    // Send the message
    $mailer->send($message);
		
    // return JSON-encoded response body
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode($data);
  } catch (Exception $e) {
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $e->getMessage());
  }
});

$app->get("/api/v1/reservation/sendmail", function () use ($app, $db, $mailer) {
	$data = [];
	$reservations = $db->reservation()
	 ->select('id, firstname, lastname, email')
	 ->where("type = ?", 'default');

	foreach ($reservations as $reservation) {
		if (filter_var($reservation['email'], FILTER_VALIDATE_EMAIL)) {
			$seats = $db->reservation_seat()
			 ->select('seat.row, seat.col, seat.seat_group.name')
			 ->where("reservation_id = ?", $reservation['id']);
			$reservationEmail = $app->view->fetch('email/reservation.php', array(
					'seats' => $seats,
					'price_per_ticket' => 150
				)
			);

			// Setting all needed info and passing in my email template.
			$message = Swift_Message::newInstance('Potvrzení rezervace vstupenek')
											->setFrom(array('vonica@vonica.cz' => 'Vonica Zlín'))
											->setTo(array($reservation['email'] => $reservation['firstname'] . ' ' . $reservation['lastname']))
											->setBody($reservationEmail)
											->setContentType("text/html");

			// Send the message
			$data[$reservation['email']] = $mailer->send($message);
		}
	}
	// return JSON-encoded response body
	$app->response()->header('Content-Type', 'application/json');
	echo json_encode($data);
});

// Notice we pass along that $mailer we created in index.php
$app->get('/api/v1/test-email', function() use ($app, $mailer, $db){

	$inserted_row['id'] = 3;
	$seats = $db->reservation_seat()
		 ->select('seat.row, seat.col, seat.seat_group.name')
     ->where("reservation_id = ?", $inserted_row['id']);
    $welcomeEmail = $app->view->fetch('email/welcome.php', array(
				'seats' => $seats,
				'price_per_ticket' => 150
			)
		);
    //$welcomeEmail = 'Toto je test';
		echo $welcomeEmail;exit;
    // Setting all needed info and passing in my email template.
    $message = Swift_Message::newInstance('Potvrzení rezervace')
                    ->setFrom(array('vonica@vonica.cz' => 'Vonica Zlín'))
                    ->setTo(array('stana@folklorista.cz' => 'Staňa Škubal'))
                    ->setBody($welcomeEmail)
                    ->setContentType("text/html");

    // Send the message
    $results = $mailer->send($message);

    // Print the results, 1 = message sent!
    print($results);

});    

// run the Slim app
$app->run();