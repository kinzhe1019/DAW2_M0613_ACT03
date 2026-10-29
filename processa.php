<?php
/*
    ACTIVITAT 3 · Formularis i tractament de dades
    processa.php: rep les dades del formulari, les valida i les mostra.

    APARTAT 3 · Rebre les dades al servidor
    - Comprovar que s'hi arriba des del formulari amb $_SERVER['REQUEST_METHOD'].
    - Recollir cada camp de $_POST amb ?? i trim().
    - Les caselles de verificació no s'envien si no es marquen: es comproven amb isset().
*/

// si algu entra escrivint la url directament, el metode es GET i no hi ha dades.
// aviso i paro la pagina amb exit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<p>Has d\'arribar aqui des del formulari. ';
    echo '<a href="registre.html">Torna-hi</a></p>';
    exit;
}

/*
    APARTAT 4 · Desar les dades en un array associatiu
    - Totes les dades de l'usuari en un sol array: la clau és el nom del camp
      i el valor, el que ha escrit l'usuari.
    - Les contrasenyes no van dins d'aquest array. Van en dues variables a part
      i no es mostren mai.
*/

// ?? '' posa un valor buit si el camp no ha arribat (aixi no surt cap warning)
// trim() treu els espais del davant i del darrere
// les caselles amb isset(): si existeix es que l'han marcat
$usuari = [
    'nom'        => trim($_POST['nom'] ?? ''),
    'cognoms'    => trim($_POST['cognoms'] ?? ''),
    'naixement'  => trim($_POST['naixement'] ?? ''),
    'telefon'    => trim($_POST['telefon'] ?? ''),
    'correu'     => trim($_POST['correu'] ?? ''),
    'usuari'     => trim($_POST['usuari'] ?? ''),
    'carrer'     => trim($_POST['carrer'] ?? ''),
    'poblacio'   => trim($_POST['poblacio'] ?? ''),
    'cp'         => trim($_POST['cp'] ?? ''),
    'tipus'      => trim($_POST['tipus'] ?? ''),
    'origen'     => trim($_POST['origen'] ?? ''),
    'butlleti'   => isset($_POST['butlleti']) ? 'Si' : 'No',
    'condicions' => isset($_POST['condicions']) ? 'Si' : 'No',
];

// les contrasenyes a part, nomes les faig servir per comparar-les
$clau = $_POST['clau'] ?? '';
$clau2 = $_POST['clau2'] ?? '';

// ampliacio 1: el correu repetit tambe va a part, nomes el faig servir per comparar
$correu2 = trim($_POST['correu2'] ?? '');

/*
    APARTAT 5 · Validar les dades
    Cada error s'afegeix a un array d'errors. Si al final és buit, tot ha anat bé.
    - Nom i cognoms: que no estiguin buits.
    - Correu: que no estigui buit i que tingui format de correu.
    - Nom d'usuari: com a mínim 4 caràcters.
    - Contrasenya: com a mínim 6 caràcters.
    - Les dues contrasenyes: que siguin idèntiques.
    - Codi postal: si s'ha omplert, que tingui exactament 5 xifres.
    - Tipus de client: que n'hagi triat un del desplegable.
    - Condicions del servei: que hagi marcat la casella.
*/

$errors = [];

// nom i cognoms: miro si estan buits
if ($usuari['nom'] === '') {
    $errors[] = 'El nom és obligatori.';
}

if ($usuari['cognoms'] === '') {
    $errors[] = 'Els cognoms són obligatoris.';
}

// correu: primer miro si esta buit, i si no ho esta miro si te format de correu
// filter_var torna false si no es un correu valid
if ($usuari['correu'] === '') {
    $errors[] = 'El correu és obligatori.';
} elseif (filter_var($usuari['correu'], FILTER_VALIDATE_EMAIL) === false) {
    $errors[] = 'El correu no té un format vàlid.';
}

// ampliacio 1: els dos correus han de ser iguals, igual que les contrasenyes
if ($usuari['correu'] !== $correu2) {
    $errors[] = 'Els correus no coincideixen.';
}

// strlen compta quants caracters te el text
if (strlen($usuari['usuari']) < 4) {
    $errors[] = "El nom d'usuari ha de tenir com a mínim 4 caràcters.";
}

if (strlen($clau) < 6) {
    $errors[] = 'La contrasenya ha de tenir com a mínim 6 caràcters.';
}

if ($clau !== $clau2) {
    $errors[] = 'Les contrasenyes no coincideixen.';
}

// el codi postal no es obligatori, nomes el miro si l'han omplert
// el patro /^[0-9]{5}$/ vol dir exactament 5 numeros i res mes
if ($usuari['cp'] !== '') {
    if (!preg_match('/^[0-9]{5}$/', $usuari['cp'])) {
        $errors[] = 'El codi postal ha de tenir exactament 5 xifres.';
    }
}

// la primera opcio del desplegable te el value buit
if ($usuari['tipus'] === '') {
    $errors[] = 'Has de triar un tipus de client.';
}

// si la casella no s'ha marcat no existeix dins $_POST
if (!isset($_POST['condicions'])) {
    $errors[] = "Has d'acceptar les condicions del servei.";
}

/*
    APARTAT 7 · Ampliacions (n'he fet dues)
    1. Afegeix un camp de confirmació del correu electrònic i comprova que coincideix
       amb el primer, com fas amb les contrasenyes.
       (el camp correu2 és a registre.html i la comprovació està a les validacions)
    2. Fes que la pàgina de resultat mostri un missatge diferent segons el tipus de
       client que hagi triat.
*/

// ampliacio 2: miro quin tipus de client ha triat i preparo un missatge per a cadascun
if ($usuari['tipus'] === 'empresa') {
    $missatgeTipus = 'Com que ets una empresa, et farem factura de totes les compres.';
} elseif ($usuari['tipus'] === 'estudiant') {
    $missatgeTipus = 'Com que ets estudiant, tens un 10% de descompte a tota la botiga.';
} else {
    $missatgeTipus = "Com que ets particular, tens l'enviament gratis a partir de 50 €.";
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <title>Registre - BitBazar</title>
    <link rel="stylesheet" href="estils.css">
</head>
<body>
<?php
/*
    APARTAT 6 · Mostrar el resum
    Si l'array d'errors no és buit, es mostren tots en una llista i no es dona
    el registre per fet. Si no hi ha cap error, es mostra:
    - Un missatge de benvinguda amb el nom i el correu de l'usuari.
    - Una taula amb totes les dades de l'array $usuari, generada amb un foreach.
    Tot el que ha escrit l'usuari i torna a la pàgina passa per htmlspecialchars().
*/
?>
<main>

<?php if (count($errors) > 0): ?>

    <!-- hi ha errors: els mostro tots en una llista -->
    <h1>No s'ha pogut crear el compte</h1>
    <ul>
        <?php foreach ($errors as $error): ?>
            <li><?= $error ?></li>
        <?php endforeach; ?>
    </ul>
    <p><a href="registre.html">Torna al formulari</a></p>

<?php else: ?>

    <!-- no hi ha errors: benvinguda i taula amb el resum -->
    <!-- htmlspecialchars fa que si algu escriu codi surti com a text i no s'executi (atac XSS) -->
    <h1>Benvingut/da, <?= htmlspecialchars($usuari['nom']) ?>!</h1>
    <p>Hem creat el teu compte amb el correu <?= htmlspecialchars($usuari['correu']) ?>.</p>

    <!-- ampliacio 2: el missatge segons el tipus de client (el text l'he escrit jo, no fa falta htmlspecialchars) -->
    <p><?= $missatgeTipus ?></p>

    <!-- el foreach fa una fila per cada camp de l'array, aixi no les escric a ma -->
    <table>
        <?php foreach ($usuari as $camp => $valor): ?>
            <tr>
                <th><?= $camp ?></th>
                <td><?= htmlspecialchars($valor) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php endif; ?>

</main>
</body>
</html>
