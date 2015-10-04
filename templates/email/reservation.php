<html>
	<head>
		<meta charset="UTF-8">
		<title>Rezervace lístků</title>
	</head>
	<body>
		<p>Dobrý den,<p>
		<p>úspěšně jste zarezervoval lístky na program k <b>75.výročí založení souboru Vonica</b> ze Zlína.</p>
		<p>Pořad proběhne ve Velkém sále Městského divadla ve Zlíně, v pátek <b>20. listopadu</b> 2015 od <b>19 hodin</b>.</p>
		<p>Máte zarezervovány tyto vstupenky:</p>
		<ul>
		<?php foreach ($seats as $seat): ?>
			<li><b><?= $seat['name'] . ' ' . $seat['row'] . '</b>, sedadlo <b>' . $seat['col']; ?></b></li>
		<?php endforeach; ?>
		</ul>
		<p>Celkem tedy <b><?= count($seats); ?></b> vstupenek za <b><?= count($seats) * $price_per_ticket ?> Kč</b>.</p>
		<p style="color: red">Vstupenky můžete zaplatit a vyzvednout osobně během tanečních zkoušek zkoušek (úterý/pátek 19h-22h) v budově Zlínského klubu 204 (tj. bývalá knihovna, <a href="https://www.google.cz/maps/place/Zl%C3%ADnsk%C3%BD+klub+204">zobrazit na mapě</a>),
			případně také dle telefonické domluvy.<br />Telefonní číslo pro vyřizování rezervací a placení lístků: <b>+420 730 678 730</b> (mezi 9-21 hod.)</p>
		
		<?php
		$now = new DateTime('+5 days');
		?>
		<p>Tato rezervace automaticky vyprší <b><?= $now->format('j. n. Y H:i'); ?></b>.</p>
		<p style="padding-left: 20px;"><i>Stanislav Škubal</i><br />
			<small><a href="mailto:stana@folklorista.cz">stana@folklorista.cz</a></small><br />
		<small>rezervace a platby vstupenek</small>
		</p>

		<p></p>
		<p></p>
		<img src="http://www.vonica.cz/wp-content/uploads/2013/11/12038991_1194594193889938_2028021714217736230_o-1024x722.jpg" alt="75 výročí souboru Vonica"/>
	</body>
</html>
