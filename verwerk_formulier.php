<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verzamelen van formuliergegevens
    $bestelnummer = $_POST['bestelnummer'];
    $voorkantTekst = $_POST['voorkantTekst'];
    $voorkantFont = $_POST['voorkantFont'];
    $voorkantKleur = $_POST['voorkantKleur'];
    $achterkantTekst = $_POST['achterkantTekst'];
    $achterkantFont = $_POST['achterkantFont'];
    $achterkantKleur = $_POST['achterkantKleur'];
	$maat = $_POST['maat'];

    // E-mailadres waar de gegevens naartoe moeten worden gestuurd
    $to = "mail@bedrukt.nl";
    
    // Onderwerp van de e-mail
    $subject = "Nieuwe bestelling - Bestelnummer: $bestelnummer";
    
    // Berichtinhoud
    $message = "Bestelnummer: $bestelnummer\n";
    $message .= "Voorkant Tekst: $voorkantTekst\n";
    $message .= "Voorkant Font: $voorkantFont\n";
    $message .= "Voorkant Kleur: $voorkantKleur\n";
    $message .= "Achterkant Tekst: $achterkantTekst\n";
    $message .= "Achterkant Font: $achterkantFont\n";
    $message .= "Achterkant Kleur: $achterkantKleur\n";
	$message .= "Geselecteerde Maat: $maat\n";

    // Verwerking van bestandsuploads
    $uploadDir = 'uploads/' . $bestelnummer . '/'; // Maak een submap aan voor het bestelnummer
	if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Maak de map aan met de juiste permissies
}

	
	
    $voorkantUpload = $_FILES['voorkantAfbeelding'];
    $achterkantUpload = $_FILES['achterkantAfbeelding'];

    $uploadedFiles = [];

    // Functie om bestanden te uploaden
    function uploadFile($file, $uploadDir) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $tmpName = $file['tmp_name'];
            $name = basename($file['name']);
            $uploadFilePath = $uploadDir . uniqid() . '-' . $name; // Unieke bestandsnaam

            // Bestandscontrole
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'psd', 'ai', 'eps'];
            $fileExtension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if (in_array($fileExtension, $allowedTypes)) {
                // Bestanden verplaatsen naar de uploads-map
                if (move_uploaded_file($tmpName, $uploadFilePath)) {
                    return $uploadFilePath; // Geef het pad van het geüploade bestand terug
                } else {
                    return false; // Fout bij uploaden
                }
            } else {
                echo "Ongeldig bestandstype. Alleen " . implode(', ', $allowedTypes) . " zijn toegestaan.";
                return false; // Ongeldig bestandstype
            }
        }
        return false; // Geen fout, maar geen succesvolle upload
    }

    // Upload bestanden
    if ($voorkantUpload) {
        $uploadedFiles['voorkant'] = uploadFile($voorkantUpload, $uploadDir);
    }
    if ($achterkantUpload) {
        $uploadedFiles['achterkant'] = uploadFile($achterkantUpload, $uploadDir);
    }

    // Voeg informatie over geüploade bestanden toe aan het e-mailbericht
    if (!empty($uploadedFiles['voorkant'])) {
        $message .= "Voorkant Bestandslocatie: " . $uploadedFiles['voorkant'] . "\n";
    }
    if (!empty($uploadedFiles['achterkant'])) {
        $message .= "Achterkant Bestandslocatie: " . $uploadedFiles['achterkant'] . "\n";
    }

    // Headers voor de e-mail
    $headers = "From: no-reply@bedrukt.nl\r\n";
    $headers .= "Reply-To: no-reply@bedrukt.nl\r\n";

    // Verzend de e-mail
    if (mail($to, $subject, $message, $headers)) {
        // Succesvolle verzending
        header("Location: verzonden.htm");
        exit();
    } else {
        // Fout bij het verzenden
        echo "Er was een probleem bij het verzenden van uw aanvraag. Probeer het alstublieft opnieuw.";
    }
} else {
    // Als het formulier niet is verzonden
    echo "Ongeldig verzoek.";
}
?>
