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
		$this->app->get("/api/v1/reservation", array($this, 'getReservationList'));
		$this->app->get("/api/v1/reservation/sendmail", array($this, 'sendMailToAll'));
		$this->app->put("/api/v1/reservation/:reservation_id", array($this, 'confirmReservation'));
		$this->app->delete("/api/v1/reservation/:reservation_id", array($this, 'cancelReservation'));
		$this->app->get("/api/v1/reservation/:reservation_id", array($this, 'getReservation'));
		$this->app->post("/api/v1/reservation", array($this, 'createReservation'));
		$this->app->get("/api/v1/test-email", array($this, 'testMail'));
	}

	function getSeats() {
		$seats = array();
		foreach ($this->db->seat()->order('id ASC') as $seat) {
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

	function getReservation($reservation_id) {
		$result = array(
			'user' => array(
				'firstname' => '',
				'lastname' => '',
				'email' => '',
				'phone' => '',
				'note' => '',
				'seats' => array()
			),
			'timestamps' => array(
				'created' => null,
				'changed' => null,
				'confirmed' => null,
				'cancelled' => null,
			)
		);

		$data = $this->db->reservation()->where('id = ?', $reservation_id)->and('type = ?', 'default')->fetch();
		if ($data) {
			$result['user']['firstname'] = $data['firstname'];
			$result['user']['lastname'] = $data['lastname'];
			$result['user']['email'] = $data['email'];
			$result['user']['phone'] = $data['phone'];
			$result['user']['note'] = $data['note'];
			$result['timestamps']['created'] = $data['created'];
			$result['timestamps']['changed'] = $data['changed'];
			$result['timestamps']['confirmed'] = $data['confirmed'];
			$result['timestamps']['cancelled'] = $data['cancelled'];

			foreach ($this->db->reservation_seat()->where('reservation.id = ?', $reservation_id)
			as $data) {
				$result['user']['seats'][]  = $data->seat['id'];
			}
		}

		$this->app->response()->header("Content-Type", "application/json");
		$json = json_encode($result);
		if (FALSE === $json) {
			echo json_last_error_msg();
		}
		echo $json;
	}

	function confirmReservation($reservation_id) {
		$result = false;
		$rows = $this->db->reservation()->where('id = ?', $reservation_id)
			->and('type = ?', 'default')->and('confirmed IS NULL OR confirmed = 0');
		if ($rows->count()) {
			$rows->update(array('confirmed' => new DateTime()));
			$result = true;
		}

		$this->app->response()->header("Content-Type", "application/json");
		$json = json_encode($result);
		if (FALSE === $json) {
			echo json_last_error_msg();
		}
		echo $json;
	}

	function cancelReservation($reservation_id) {
		$result = false;
		$rows = $this->db->reservation()->where('id = ?', $reservation_id)
			->and('type = ?', 'default')->and('cancelled IS NULL OR cancelled = 0');
		if ($rows->count()) {
			$rows->update(array('cancelled' => new DateTime()));
			$result = true;
		}

		$this->app->response()->header("Content-Type", "application/json");
		$json = json_encode($result);
		if (FALSE === $json) {
			echo json_last_error_msg();
		}
		echo $json;
	}

	function getReservationList() {
		$result = array();

		foreach ($this->db->reservation()->where('type = ?', 'default')->order('lastname ASC, firstname ASC') as $data) {
			$result[] = array(
				'id' => $data['id'],
				'firstname' => $data['firstname'],
				'lastname' => $data['lastname'],
				'created' => $data['created'],
				'confirmed' => $data['confirmed'],
				'cancelled' => $data['cancelled'],
				'seat_count' => $this->db->reservation_seat()->where('reservation.id = ?', $data['id'])->count()
			);
		}

		$this->app->response()->header("Content-Type", "application/json");
		$json = json_encode($result);
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