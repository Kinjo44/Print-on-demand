<?php
const RECIPIENT_EMAIL = 'cyclone44@wanadoo.fr';
const MAX_FILE_SIZE = 15 * 1024 * 1024;
const ALLOWED_EXTENSIONS = ['stl', 'stp', 'step'];

function clean_value(string $value): string
{
    return trim(str_replace(["\r", "\n"], ' ', $value));
}

function fail_request(string $message, int $statusCode = 400): void
{
    http_response_code($statusCode);
    echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail_request('Méthode non autorisée.');
}

$nom = clean_value($_POST['nom'] ?? '');
$prenom = clean_value($_POST['prenom'] ?? '');
$description = trim($_POST['description'] ?? '');
$urgence = clean_value($_POST['urgence'] ?? '');
$technologie = clean_value($_POST['technologie'] ?? '');
$couleur = clean_value($_POST['couleur'] ?? 'Non précisée');
$commentaire = trim($_POST['commentaire'] ?? 'Aucun commentaire');

if ($nom === '' || $prenom === '' || $description === '' || $urgence === '' || $technologie === '') {
    fail_request('Merci de remplir tous les champs obligatoires.');
}

if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    fail_request('Le téléversement dépasse la limite autorisée par le serveur. Merci de réduire la taille des fichiers ou d’augmenter post_max_size/upload_max_filesize.', 413);
}

if (!isset($_FILES['fichiers']) || !is_array($_FILES['fichiers']['name'])) {
    fail_request('Merci d’ajouter au moins un fichier .stl, .stp ou .step.');
}

$attachments = [];
$fileCount = count($_FILES['fichiers']['name']);

for ($index = 0; $index < $fileCount; $index++) {
    $error = $_FILES['fichiers']['error'][$index];

    $originalName = basename($_FILES['fichiers']['name'][$index] ?? 'fichier');

    if ($error !== UPLOAD_ERR_OK) {
        $uploadMessages = [
            UPLOAD_ERR_INI_SIZE => "Le fichier {$originalName} dépasse la limite configurée sur le serveur.",
            UPLOAD_ERR_FORM_SIZE => "Le fichier {$originalName} dépasse la limite autorisée par le formulaire.",
            UPLOAD_ERR_PARTIAL => "Le fichier {$originalName} n’a été téléversé que partiellement.",
            UPLOAD_ERR_NO_FILE => "Aucun fichier n’a été reçu pour {$originalName}.",
            UPLOAD_ERR_NO_TMP_DIR => 'Le dossier temporaire de téléversement est manquant sur le serveur.',
            UPLOAD_ERR_CANT_WRITE => 'Le serveur n’a pas pu écrire le fichier téléversé.',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a interrompu le téléversement.',
        ];

        fail_request($uploadMessages[$error] ?? 'Un fichier n’a pas pu être téléversé correctement.');
    }

    $originalName = basename($_FILES['fichiers']['name'][$index]);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $tmpName = $_FILES['fichiers']['tmp_name'][$index];
    $size = (int) $_FILES['fichiers']['size'][$index];

    if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
        fail_request('Seuls les fichiers .stl, .stp ou .step sont acceptés.');
    }

    if ($size <= 0 || $size > MAX_FILE_SIZE) {
        fail_request('Chaque fichier doit faire moins de 15 Mo.');
    }

    $attachments[] = [
        'name' => $originalName,
        'content' => chunk_split(base64_encode(file_get_contents($tmpName) ?: '')),
        'type' => mime_content_type($tmpName) ?: 'application/octet-stream',
    ];
}

$boundary = '=_3d_order_' . bin2hex(random_bytes(16));
$subject = 'Nouvelle demande d’impression 3D - ' . $prenom . ' ' . $nom;

$message = "Nouvelle demande d'impression 3D\n\n"
    . "Nom : {$nom}\n"
    . "Prénom : {$prenom}\n"
    . "Urgence : {$urgence}\n"
    . "Technologie : {$technologie}\n"
    . "Couleur de préférence : {$couleur}\n\n"
    . "Description du besoin :\n{$description}\n\n"
    . "Commentaire :\n{$commentaire}\n\n"
    . 'Fichiers joints : ' . implode(', ', array_column($attachments, 'name')) . "\n";

$headers = [
    'MIME-Version: 1.0',
    'From: Formulaire Impression 3D <no-reply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . '>',
    'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
];

$body = "--{$boundary}\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n";
$body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
$body .= $message . "\r\n";

foreach ($attachments as $attachment) {
    $safeName = addcslashes($attachment['name'], '"\\');
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: {$attachment['type']}; name=\"{$safeName}\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n";
    $body .= "Content-Disposition: attachment; filename=\"{$safeName}\"\r\n\r\n";
    $body .= $attachment['content'] . "\r\n";
}

$body .= "--{$boundary}--";

if (!mail(RECIPIENT_EMAIL, $subject, $body, implode("\r\n", $headers))) {
    http_response_code(500);
    echo 'Votre demande est complète, mais l’envoi du mail a échoué. Merci de réessayer plus tard.';
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><html lang="fr"><meta charset="utf-8"><title>Demande envoyée</title><link rel="stylesheet" href="styles.css"><main class="page-shell"><section class="form-card"><h1>Demande envoyée</h1><p>Merci, votre demande d’impression 3D a bien été transmise.</p><a href="index.html">Retour au formulaire</a></section></main></html>';
