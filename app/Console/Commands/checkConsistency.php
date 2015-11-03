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
		$nombres = "Aarón
Abdón
Abel
Abelardo
Abrahán
Absalón
Acacio
Adalberto
Adán
Adela
Adelaida
Adolfo
Adón
Adrián
Agustín
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
Álvaro
Amadeo
Amaro
Ambrosio
Amelia
Amparo
Ana
Ananías
Anastasia
Anatolio
Andrea
Andrés
Ángel
Ángela
Ángeles
Aniano
Anna
Anselmo
Antero
Antonia
Antonio
Aquiles
Araceli
Aránzazu
Arcadio
Aresio
Ariadna
Aristides
Arnaldo
Artemio
Arturo
Ascensión
Asunción
Atanasio
Augusto
Áurea
Aurelia
Aureliano
Aurelio
Aurora
Baldomero
Balduino
Baltasar
Bárbara
Bartolomé
Basileo
Beatriz
Begoña
Belén
Beltrán
Benedicto
Benigno
Benito
Benjamín
Bernabé
Bernarda
Bernardo
Blanca
Blas
Bonifacio
Borja
Bruno
Calixto
Camilo
Cándida
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
César
Cesáreo
Cipriano
Cirilo
Cirino
Ciro
Clara
Claudia
Claudio
Cleofás
Clotilde
Colombo
Columba
Columbano
Concepción
Conrado
Constancio
Constantino
Consuelo
Cosme
Cristian
Cristina
Cristóbal
Daciano
Dacio
Dámaso
Damián
Daniel
Dario
David
Demócrito
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
Efrén
Elena
Elías
Elisa
Eliseo
Elvira
Emilia
Emiliano
Emilio
Encarnación
Enrique
Epifanía
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
Fabián
Fabio
Fabiola
Facundo
Fátima
Faustino
Fausto
Federico
Feliciano
Felipe
Félix
Fermín
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
Germán
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
Guzmán
Héctor
Heliodoro
Heraclio
Heriberto
Hilarión
Hildegarda
Homero
Honorato
Honorio
Hugo
Humberto
Ifigenia
Ignacio
Ildefonso
Inés
Inmaculada
Inocencio
Irene
Ireneo
Isaac
Isabel
Isaías
Isidro
Ismael
Iván
Jacinto
Jacob
Jacobo
Jaime
Jaume
Javier
Jeremías
Jerónimo
Jesús
Joan
Joaquím
Joaquín
Joel
Jonás
Jonathan
Jordi
Jorge
Josafat
José
Josefa
Josefina
Josep
Josué
Juan
Juana
Julia
Julián
Julio
Justino
Juvenal
Ladislao
Laura
Laureano
Lázaro
Leandro
Leocadia
León
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
Lucía
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
María
Mariano
Marina
Mario
Marta
Martín
Mateo
Matías
Matilde
Mauricio
Maximiliano
Melchor
Mercedes
Miguel
Milagros
Miqueas
Míriam
Mohamed
Moisés
Mónica
Montserrat
Narciso
Natalia
Natividad
Nazario
Nemesio
Nicanor
Nicodemo
Nicolás
Nicomedes
Nieves
Noé
Noelia
Norberto
Nuria
Octavio
Odón
Olga
Onésimo
Orestes
Oriol
Oscar
Óscar
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
Pío
Poncio
Porfirio
Primo
Priscila
Probo
Purificación
Rafael
Raimundo
Ramiro
Ramón
Raquel
Raúl
Rebeca
Reinaldo
Remedios
Renato
Ricardo
Rigoberto
Rita
Roberto
Rocío
Rodrigo
Rogelio
Román
Romualdo
Roque
Rosa
Rosalia
Rosario
Rosendo
Rubén
Rufo
Ruperto
Salomé
Salomón
Salvador
Salvio
Samuel
Sandra
Sansón
Santiago
Sara
Sebastián
Segismundo
Sergio
Severino
Silvia
Simeón
Simón
Siro
Sixto
Sofía
Soledad
Sonia
Susana
Tadeo
Tarsicio
Teodora
Teodosia
Teófanes
Teófila
Teresa
Timoteo
Tito
Tobías
Tomas
Tomás
Toribio
Trinidad
Ubaldo
Urbano
Úrsula
Valentín
Valeriano
Vanesa
Velerio
Venancio
Verónica
Vicenta
Vicente
Víctor
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
Zacarías
Zaqueo";
		$apellidos = "Aguilar
Alonso
Álvarez
Arias
Benítez
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
Cortés
Crespo
Cruz
Delgado
Díaz
Díez
Domínguez
Durán
Esteban
Fernández
Ferrer
Flores
Fuentes
Gallardo
Gallego
García
Garrido
Gil
Giménez
Gómez
González
Guerrero
Gutiérrez
Hernández
Herrera
Herrero
Hidalgo
Ibáñez
Iglesias
Jiménez
León
López
Lorenzo
Lozano
Marín
Márquez
Martín
Martínez
Medina
Méndez
Molina
Montero
Montoro
Mora
Morales
Moreno
Moya
Muñoz
Navarro
Nieto
Núñez
Ortega
Ortiz
Parra
Pascual
Pastor
Peña
Pérez
Prieto
Ramírez
Ramos
Rey
Reyes
Rodríguez
Román
Romero
Rubio
Ruiz
Sáez
Sánchez
Santana
Santiago
Santos
Sanz
Serrano
Soler
Soto
Suárez
Torres
Vargas
Vázquez
Vega
Velasco
Vicente
Vidal";
		$nombres = $this->normlizr($nombres);
		$splited = explode("\n", $nombres);

		foreach($splited as $split){
			echo $split."\n";
		}
		$pd = new Pipedrive('German de Val Gomez Pérez');
	  	//$reload = new SyncPrestashopProducts(4);
		//$reload->handle();
	}
	public function normlizr($chain)
	{
		$replace = [
			'&lt;' => '', '&gt;' => '', '&#039;' => '', '&amp;' => '',
			'&quot;' => '', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae',
			'&Auml;' => 'A', 'Å' => 'A', '?' => 'A', '?' => 'A', '?' => 'A', 'Æ' => 'Ae',
			'Ç' => 'C', '?' => 'C', '?' => 'C', '?' => 'C', '?' => 'C', '?' => 'D', '?' => 'D',
			'Ğ' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', '?' => 'E',
			'?' => 'E', '?' => 'E', '?' => 'E', '?' => 'E', '?' => 'G', '?' => 'G',
			'?' => 'G', '?' => 'G', '?' => 'H', '?' => 'H', 'Ì' => 'I', 'Í' => 'I',
			'Î' => 'I', 'Ï' => 'I', '?' => 'I', '?' => 'I', '?' => 'I', '?' => 'I',
			'?' => 'I', '?' => 'IJ', '?' => 'J', '?' => 'K', '?' => 'K', '?' => 'K',
			'?' => 'K', '?' => 'K', '?' => 'K', 'Ñ' => 'N', '?' => 'N', '?' => 'N',
			'?' => 'N', '?' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
			'Ö' => 'Oe', '&Ouml;' => 'Oe', 'Ø' => 'O', '?' => 'O', '?' => 'O', '?' => 'O',
			'Œ' => 'OE', '?' => 'R', '?' => 'R', '?' => 'R', '?' => 'S', 'Š' => 'S',
			'?' => 'S', '?' => 'S', '?' => 'S', '?' => 'T', '?' => 'T', '?' => 'T',
			'?' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', '?' => 'U',
			'&Uuml;' => 'Ue', '?' => 'U', '?' => 'U', '?' => 'U', '?' => 'U', '?' => 'U',
			'?' => 'W', 'İ' => 'Y', '?' => 'Y', 'Ÿ' => 'Y', '?' => 'Z', '' => 'Z',
			'?' => 'Z', 'Ş' => 'T', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
			'ä' => 'ae', '&auml;' => 'ae', 'å' => 'a', '?' => 'a', '?' => 'a', '?' => 'a',
			'æ' => 'ae', 'ç' => 'c', '?' => 'c', '?' => 'c', '?' => 'c', '?' => 'c',
			'?' => 'd', '?' => 'd', 'ğ' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e',
			'ë' => 'e', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'e',
			'ƒ' => 'f', '?' => 'g', '?' => 'g', '?' => 'g', '?' => 'g', '?' => 'h',
			'?' => 'h', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', '?' => 'i',
			'?' => 'i', '?' => 'i', '?' => 'i', '?' => 'i', '?' => 'ij', '?' => 'j',
			'?' => 'k', '?' => 'k', '?' => 'l', '?' => 'l', '?' => 'l', '?' => 'l',
			'?' => 'l', 'ñ' => 'n', '?' => 'n', '?' => 'n', '?' => 'n', '?' => 'n',
			'?' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'oe',
			'&ouml;' => 'oe', 'ø' => 'o', '?' => 'o', '?' => 'o', '?' => 'o', 'œ' => 'oe',
			'?' => 'r', '?' => 'r', '?' => 'r', 'š' => 's', 'ù' => 'u', 'ú' => 'u',
			'û' => 'u', 'ü' => 'ue', '?' => 'u', '&uuml;' => 'ue', '?' => 'u', '?' => 'u',
			'?' => 'u', '?' => 'u', '?' => 'u', '?' => 'w', 'ı' => 'y', 'ÿ' => 'y',
			'?' => 'y', '' => 'z', '?' => 'z', '?' => 'z', 'ş' => 't', 'ß' => 'ss',
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
