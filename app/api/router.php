<?php

//In the Loader class
class Router {
	protected $app;
	protected $db;
	protected $mailer;

	public function __construct($app, $db, $mailer) {
		$this->app = $app;
		$this->db = $db;
		$this->mailer = $mailer;
	}

	public function run() {
		$this->app->get("/api/v1/seat", array($this, 'getSeats'));
		$this->app->get("/api/v1/seat/reserved", array($this, 'getReservedSeats'));
		$this->app->post("/api/v1/reservation", array($this, 'createReservation'));
		$this->app->get("/api/v1/reservation/sendmail", array($this, 'sendMailToAll'));
		$this->app->get("/api/v1/test-email", array($this, 'testMail'));
	}

	function getSeats() {
		$seats = array();
		foreach ($this->db->seat() as $seat) {
			$seats[]  = array(
				"id" => $seat['id'],
				"row" => $seat['row'],
				"col" => $seat['col'],
				"left" => $seat['css_left'],
				"top" => $seat['css_top'],
				"seat_group" => $seat->seat_group['name']
			);
		}
		$this->app->response()->header("Content-Type", "application/json");
		$json = json_encode($seats);
		if (FALSE === $json) {
			echo json_last_error_msg();
		}
		echo $json;
	}

	function getReservedSeats() {
		$reservations = array();
		foreach ($this->db->reservation_seat()
		->where('reservation.cancelled IS NULL')
		as $reservation) {
			$reservations[]  = array(
		"id" => $reservation->seat['id'],
		"type" => $reservation->reservation['type']
			);
		}
		$this->app->response()->header("Content-Type", "application/json");
		$json = json_encode($reservations);
		if (FALSE === $json) {
			echo json_last_error_msg();
		}
		echo $json;
	}

	function createReservation() {
		try {
			// get and decode JSON request body
			$request = $this->app->request();
			$body = $request->getBody();
			$data = json_decode($body);

			$inserted_row = $this->db->reservation->insert(array(
				'firstname' => $data->firstname,
				'lastname' => $data->lastname,
				'email' => $data->email,
				'phone' => $data->phone,
				'note' => $data->note,
				'created' => new \DateTime()
			));

			$reserved_seats = $this->db->reservation_seat()
			 ->where("seat_id IN (?)", implode(',', $data->seats));

			if ($reserved_seats->count()) {
				$this->app->response()->status(400);
				$this->app->response()->header('X-Status-Reason', 'Already registered');	
				return;
			}

			foreach ($data->seats as $seat_id) {
				$this->db->reservation_seat->insert(array(
					'reservation_id' => $inserted_row['id'],
					'seat_id' => $seat_id,
					'created' => new \DateTime()
				));
			}

			$seats = $this->db->reservation_seat()
			 ->select('seat.row, seat.col, seat.seat_group.name')
			 ->where("reservation_id = ?", $inserted_row['id']);
			$reservationEmail = $this->app->view->fetch('email/reservation.php', array(
				'seats' => $seats,
				'price_per_ticket' => 150
			));

			// Setting all needed info and passing in my email template.
			$message = Swift_Message::newInstance('Potvrzení rezervace vstupenek')
				->setFrom(array('vonica@vonica.cz' => 'Vonica Zlín'))
				->setTo(array($data->email => $data->firstname . ' ' . $data->lastname))
				->setBody($reservationEmail)
				->setContentType("text/html");

			// Send the message
			$this->mailer->send($message);

			// return JSON-encoded response body
			$this->app->response()->header('Content-Type', 'application/json');
			echo json_encode($data);
		} catch (Exception $e) {
			$this->app->response()->status(400);
			$this->app->response()->header('X-Status-Reason', $e->getMessage());
		}
	}

	function sendMailToAll() {
		$data = [];
		$reservations = $this->db->reservation()
		 ->select('id, firstname, lastname, email')
		 ->where("type = ?", 'default');

		foreach ($reservations as $reservation) {
			if (filter_var($reservation['email'], FILTER_VALIDATE_EMAIL)) {
				$seats = $this->db->reservation_seat()
				 ->select('seat.row, seat.col, seat.seat_group.name')
				 ->where("reservation_id = ?", $reservation['id']);
				$reservationEmail = $this->app->view->fetch('email/reservation.php', array(
					'seats' => $seats,
					'price_per_ticket' => 150
				));

				// Setting all needed info and passing in my email template.
				$message = Swift_Message::newInstance('Potvrzení rezervace vstupenek')
					->setFrom(array('vonica@vonica.cz' => 'Vonica Zlín'))
					->setTo(array($reservation['email'] => $reservation['firstname'] . ' ' . $reservation['lastname']))
					->setBody($reservationEmail)
					->setContentType("text/html");

				// Send the message
				$data[$reservation['email']] = $this->mailer->send($message);
			}
		}
		// return JSON-encoded response body
		$this->app->response()->header('Content-Type', 'application/json');
		echo json_encode($data);
	}

	// Notice we pass along that $this->mailer we created in index.php
	function testMail() {
		$inserted_row['id'] = 3;
		$seats = $this->db->reservation_seat()
			 ->select('seat.row, seat.col, seat.seat_group.name')
			 ->where("reservation_id = ?", $inserted_row['id']);
		$welcomeEmail = $this->app->view->fetch('email/reservation.php', array(
			'seats' => $seats,
			'price_per_ticket' => 150
		));
		// Setting all needed info and passing in my email template.
		$message = Swift_Message::newInstance('Potvrzení rezervace')
			->setFrom(array('vonica@vonica.cz' => 'Vonica Zlín'))
			->setTo(array('stana@folklorista.cz' => 'Staňa Škubal'))
			->setBody($welcomeEmail)
			->setContentType("text/html");

		// Send the message
		$results = $this->mailer->send($message);

		// Print the results, 1 = message sent!
		print($results);
	}
}