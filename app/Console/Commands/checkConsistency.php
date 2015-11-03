<?php namespace pintegration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Tools\Tools;
use Pipedrive\Pipedrive;


class checkConsistency extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue, SerializesModels;

	protected $name = 'command:checkconsistency';
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->handle();
		parent::__construct();
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$nombres = "Aar�n
Abd�n
Abel
Abelardo
Abrah�n
Absal�n
Acacio
Adalberto
Ad�n
Adela
Adelaida
Adolfo
Ad�n
Adri�n
Agust�n
Aitor
Alba
Albert
Alberto
Albina
Alejandra
Alejandro
Alejo
Alfonso
Alfredo
Alicia
Alipio
Almudena
Alonso
�lvaro
Amadeo
Amaro
Ambrosio
Amelia
Amparo
Ana
Anan�as
Anastasia
Anatolio
Andrea
Andr�s
�ngel
�ngela
�ngeles
Aniano
Anna
Anselmo
Antero
Antonia
Antonio
Aquiles
Araceli
Ar�nzazu
Arcadio
Aresio
Ariadna
Aristides
Arnaldo
Artemio
Arturo
Ascensi�n
Asunci�n
Atanasio
Augusto
�urea
Aurelia
Aureliano
Aurelio
Aurora
Baldomero
Balduino
Baltasar
B�rbara
Bartolom�
Basileo
Beatriz
Bego�a
Bel�n
Beltr�n
Benedicto
Benigno
Benito
Benjam�n
Bernab�
Bernarda
Bernardo
Blanca
Blas
Bonifacio
Borja
Bruno
Calixto
Camilo
C�ndida
Carina
Carlos
Carmelo
Carmen
Carolina
Casiano
Casimiro
Casio
Catalina
Cayetano
Cayo
Cecilia
Ceferino
Celia
Celina
Celso
C�sar
Ces�reo
Cipriano
Cirilo
Cirino
Ciro
Clara
Claudia
Claudio
Cleof�s
Clotilde
Colombo
Columba
Columbano
Concepci�n
Conrado
Constancio
Constantino
Consuelo
Cosme
Cristian
Cristina
Crist�bal
Daciano
Dacio
D�maso
Dami�n
Daniel
Dario
David
Dem�crito
Diego
Dimas
Dolores
Domingo
Donato
Dorotea
Edgar
Edmundo
Eduardo
Eduvigis
Efr�n
Elena
El�as
Elisa
Eliseo
Elvira
Emilia
Emiliano
Emilio
Encarnaci�n
Enrique
Epifan�a
Erico
Ernesto
Esdras
Esiquio
Esperanza
Esteban
Ester
Esther
Eugenia
Eugenio
Eulalia
Eusebio
Eva
Evaristo
Ezequiel
Fabi�n
Fabio
Fabiola
Facundo
F�tima
Faustino
Fausto
Federico
Feliciano
Felipe
F�lix
Ferm�n
Fernando
Fidel
Fortunato
Francesc
Francisca
Francisco
Fulgencio
Gabriel
Gema
Genoveva
Gerardo
Germ�n
Gertrudis
Gisela
Gloria
Godofredo
Gonzalo
Gregorio
Guadalupe
Guido
Guillermo
Gustavo
Guzm�n
H�ctor
Heliodoro
Heraclio
Heriberto
Hilari�n
Hildegarda
Homero
Honorato
Honorio
Hugo
Humberto
Ifigenia
Ignacio
Ildefonso
In�s
Inmaculada
Inocencio
Irene
Ireneo
Isaac
Isabel
Isa�as
Isidro
Ismael
Iv�n
Jacinto
Jacob
Jacobo
Jaime
Jaume
Javier
Jerem�as
Jer�nimo
Jes�s
Joan
Joaqu�m
Joaqu�n
Joel
Jon�s
Jonathan
Jordi
Jorge
Josafat
Jos�
Josefa
Josefina
Josep
Josu�
Juan
Juana
Julia
Juli�n
Julio
Justino
Juvenal
Ladislao
Laura
Laureano
L�zaro
Leandro
Leocadia
Le�n
Leonardo
Leoncio
Leonor
Leopoldo
Lidia
Liduvina
Lino
Lorena
Lorenzo
Lourdes
Lucano
Lucas
Luc�a
Luciano
Lucrecia
Luis
Luisa
Luz
Macario
Magdalena
Manuel
Manuela
Mar
Marc
Marcelino
Marcelo
Marcial
Marciano
Marcos
Margarita
Mar�a
Mariano
Marina
Mario
Marta
Mart�n
Mateo
Mat�as
Matilde
Mauricio
Maximiliano
Melchor
Mercedes
Miguel
Milagros
Miqueas
M�riam
Mohamed
Mois�s
M�nica
Montserrat
Narciso
Natalia
Natividad
Nazario
Nemesio
Nicanor
Nicodemo
Nicol�s
Nicomedes
Nieves
No�
Noelia
Norberto
Nuria
Octavio
Od�n
Olga
On�simo
Orestes
Oriol
Oscar
�scar
Oseas
Oswaldo
Otilia
Oto
Pablo
Pancracio
Pascual
Patricia
Patricio
Paula
Pedro
Petronila
Pilar
P�o
Poncio
Porfirio
Primo
Priscila
Probo
Purificaci�n
Rafael
Raimundo
Ramiro
Ram�n
Raquel
Ra�l
Rebeca
Reinaldo
Remedios
Renato
Ricardo
Rigoberto
Rita
Roberto
Roc�o
Rodrigo
Rogelio
Rom�n
Romualdo
Roque
Rosa
Rosalia
Rosario
Rosendo
Rub�n
Rufo
Ruperto
Salom�
Salom�n
Salvador
Salvio
Samuel
Sandra
Sans�n
Santiago
Sara
Sebasti�n
Segismundo
Sergio
Severino
Silvia
Sime�n
Sim�n
Siro
Sixto
Sof�a
Soledad
Sonia
Susana
Tadeo
Tarsicio
Teodora
Teodosia
Te�fanes
Te�fila
Teresa
Timoteo
Tito
Tob�as
Tomas
Tom�s
Toribio
Trinidad
Ubaldo
Urbano
�rsula
Valent�n
Valeriano
Vanesa
Velerio
Venancio
Ver�nica
Vicenta
Vicente
V�ctor
Victoria
Victorino
Victorio
Vidal
Virgilio
Virginia
Vladimiro
Wilfredo
Xavier
Yolanda
Zacar�as
Zaqueo";
		$apellidos = "Aguilar
Alonso
�lvarez
Arias
Ben�tez
Blanco
Blesa
Bravo
Caballero
Cabrera
Calvo
Cambil
Campos
Cano
Carmona
Carrasco
Castillo
Castro
Cort�s
Crespo
Cruz
Delgado
D�az
D�ez
Dom�nguez
Dur�n
Esteban
Fern�ndez
Ferrer
Flores
Fuentes
Gallardo
Gallego
Garc�a
Garrido
Gil
Gim�nez
G�mez
Gonz�lez
Guerrero
Guti�rrez
Hern�ndez
Herrera
Herrero
Hidalgo
Ib��ez
Iglesias
Jim�nez
Le�n
L�pez
Lorenzo
Lozano
Mar�n
M�rquez
Mart�n
Mart�nez
Medina
M�ndez
Molina
Montero
Montoro
Mora
Morales
Moreno
Moya
Mu�oz
Navarro
Nieto
N��ez
Ortega
Ortiz
Parra
Pascual
Pastor
Pe�a
P�rez
Prieto
Ram�rez
Ramos
Rey
Reyes
Rodr�guez
Rom�n
Romero
Rubio
Ruiz
S�ez
S�nchez
Santana
Santiago
Santos
Sanz
Serrano
Soler
Soto
Su�rez
Torres
Vargas
V�zquez
Vega
Velasco
Vicente
Vidal";
		$nombres = $this->normlizr($nombres);
		$splited = explode("\n", $nombres);

		foreach($splited as $split){
			echo $split."\n";
		}
		$pd = new Pipedrive('German de Val Gomez P�rez');
	  	//$reload = new SyncPrestashopProducts(4);
		//$reload->handle();
	}
	public function normlizr($chain)
	{
		$replace = [
			'&lt;' => '', '&gt;' => '', '&#039;' => '', '&amp;' => '',
			'&quot;' => '', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'Ae',
			'&Auml;' => 'A', '�' => 'A', '?' => 'A', '?' => 'A', '?' => 'A', '�' => 'Ae',
			'�' => 'C', '?' => 'C', '?' => 'C', '?' => 'C', '?' => 'C', '?' => 'D', '?' => 'D',
			'�' => 'D', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'E', '?' => 'E',
			'?' => 'E', '?' => 'E', '?' => 'E', '?' => 'E', '?' => 'G', '?' => 'G',
			'?' => 'G', '?' => 'G', '?' => 'H', '?' => 'H', '�' => 'I', '�' => 'I',
			'�' => 'I', '�' => 'I', '?' => 'I', '?' => 'I', '?' => 'I', '?' => 'I',
			'?' => 'I', '?' => 'IJ', '?' => 'J', '?' => 'K', '?' => 'K', '?' => 'K',
			'?' => 'K', '?' => 'K', '?' => 'K', '�' => 'N', '?' => 'N', '?' => 'N',
			'?' => 'N', '?' => 'N', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O',
			'�' => 'Oe', '&Ouml;' => 'Oe', '�' => 'O', '?' => 'O', '?' => 'O', '?' => 'O',
			'�' => 'OE', '?' => 'R', '?' => 'R', '?' => 'R', '?' => 'S', '�' => 'S',
			'?' => 'S', '?' => 'S', '?' => 'S', '?' => 'T', '?' => 'T', '?' => 'T',
			'?' => 'T', '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'Ue', '?' => 'U',
			'&Uuml;' => 'Ue', '?' => 'U', '?' => 'U', '?' => 'U', '?' => 'U', '?' => 'U',
			'?' => 'W', '�' => 'Y', '?' => 'Y', '�' => 'Y', '?' => 'Z', '�' => 'Z',
			'?' => 'Z', '�' => 'T', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a',
			'�' => 'ae', '&auml;' => 'ae', '�' => 'a', '?' => 'a', '?' => 'a', '?' => 'a',
			'�' => 'ae', '�' => 'c', '?' => 'c', '?' => 'c', '?' => 'c', '?' => 'c',
			'?' => 'd', '?' => 'd', '�' => 'd', '�' => 'e', '�' => 'e', '�' => 'e',
			'�' => 'e', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'e',
			'�' => 'f', '?' => 'g', '?' => 'g', '?' => 'g', '?' => 'g', '?' => 'h',
			'?' => 'h', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'i', '?' => 'i',
			'?' => 'i', '?' => 'i', '?' => 'i', '?' => 'i', '?' => 'ij', '?' => 'j',
			'?' => 'k', '?' => 'k', '?' => 'l', '?' => 'l', '?' => 'l', '?' => 'l',
			'?' => 'l', '�' => 'n', '?' => 'n', '?' => 'n', '?' => 'n', '?' => 'n',
			'?' => 'n', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'oe',
			'&ouml;' => 'oe', '�' => 'o', '?' => 'o', '?' => 'o', '?' => 'o', '�' => 'oe',
			'?' => 'r', '?' => 'r', '?' => 'r', '�' => 's', '�' => 'u', '�' => 'u',
			'�' => 'u', '�' => 'ue', '?' => 'u', '&uuml;' => 'ue', '?' => 'u', '?' => 'u',
			'?' => 'u', '?' => 'u', '?' => 'u', '?' => 'w', '�' => 'y', '�' => 'y',
			'?' => 'y', '�' => 'z', '?' => 'z', '?' => 'z', '�' => 't', '�' => 'ss',
			'?' => 'ss', '??' => 'iy', '?' => 'A', '?' => 'B', '?' => 'V', '?' => 'G',
			'?' => 'D', '?' => 'E', '?' => 'YO', '?' => 'ZH', '?' => 'Z', '?' => 'I',
			'?' => 'Y', '?' => 'K', '?' => 'L', '?' => 'M', '?' => 'N', '?' => 'O',
			'?' => 'P', '?' => 'R', '?' => 'S', '?' => 'T', '?' => 'U', '?' => 'F',
			'?' => 'H', '?' => 'C', '?' => 'CH', '?' => 'SH', '?' => 'SCH', '?' => '',
			'?' => 'Y', '?' => '', '?' => 'E', '?' => 'YU', '?' => 'YA', '?' => 'a',
			'?' => 'b', '?' => 'v', '?' => 'g', '?' => 'd', '?' => 'e', '?' => 'yo',
			'?' => 'zh', '?' => 'z', '?' => 'i', '?' => 'y', '?' => 'k', '?' => 'l',
			'?' => 'm', '?' => 'n', '?' => 'o', '?' => 'p', '?' => 'r', '?' => 's',
			'?' => 't', '?' => 'u', '?' => 'f', '?' => 'h', '?' => 'c', '?' => 'ch',
			'?' => 'sh', '?' => 'sch', '?' => '', '?' => 'y', '?' => '', '?' => 'e',
			'?' => 'yu', '?' => 'ya'
		];

		$chain= str_replace(array_keys($replace), $replace, $chain);
		$chain = strtolower($chain);
		return $chain;
	}
}
