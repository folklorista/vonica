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
		<p><span style="color: red">Vstupenky můžete zaplatit a vyzvednout <b>osobně během tanečních zkoušek</b> (úterý/pátek 19h-22h) v budově Zlínského klubu 204 (tj. bývalá knihovna, <a href="https://www.google.cz/maps/place/Zl%C3%ADnsk%C3%BD+klub+204">zobrazit na mapě</a>).</span><br />
					Nebo je možné se na způsobu úhrady a vyzvednutí vstupenek domluvit na telefonním čísle <i>+420 730 678 730</i> (určeno zejména pro mimozlínské diváky, volejte mezi 9-21 hod.)
		</p>
		
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
		<img src="http://www.vonica.cz/wp-content/uploads/2015/10/pozvánka-A5.jpg" alt="75 výročí souboru Vonica" width="100%" style="width:100%"/>
	</body>
</html>
