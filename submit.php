<?php
// Vérifie si les données sont envoyées par POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupère les valeurs du formulaire
    $nom = isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : 'Inconnu';
    $prenom = isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : 'Inconnu';
    $age = isset($_POST['age']) ? htmlspecialchars($_POST['age']) : 'Inconnu';
    $lieu_residence = isset($_POST['lieu_residence']) ? htmlspecialchars($_POST['lieu_residence']) : 'Inconnu';
    $metier_grade = isset($_POST['metier_grade']) ? htmlspecialchars($_POST['metier_grade']) : 'Inconnu';
    $role_particulier = isset($_POST['role_description']) ? htmlspecialchars($_POST['role_description']) : 'Non spécifié';
    $test_rp = isset($_POST['action']) ? htmlspecialchars($_POST['action']) : 'Non spécifié';

    // Traitement de l'image (si elle est envoyée)
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = 'uploads/';  // Dossier pour stocker les images
        // Vérifie si le dossier existe sinon le créer
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Récupère le nom de l'image et la place dans un format sécurisé
        $imageName = basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $imageName;

        // Déplace l'image téléchargée dans le dossier "uploads"
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $uploadFile;
        }
    }

    // Crée une fiche sous forme de tableau
    $fiche = [
        'nom' => $nom,
        'prenom' => $prenom,
        'age' => $age,
        'lieu_residence' => $lieu_residence,
        'metier_grade' => $metier_grade,
        'role_particulier' => $role_particulier,
        'test_rp' => $test_rp,
        'image' => $image,
        'date' => date('Y-m-d H:i:s')
    ];

    // Récupère les fiches précédemment enregistrées (s'il y en a)
    $fiches = [];
    $jsonFile = 'fiches.json';
    if (file_exists($jsonFile)) {
        $jsonData = file_get_contents($jsonFile);
        $fiches = json_decode($jsonData, true);
    }

    // Ajoute la nouvelle fiche au tableau
    $fiches[] = $fiche;

    // Sauvegarde toutes les fiches dans le fichier JSON
    file_put_contents($jsonFile, json_encode($fiches, JSON_PRETTY_PRINT));

    // Format du message Discord selon l'exemple fourni
    $embed = [
        "embeds" => [
            [
                "title" => "ℹ️ • Information(s) :",
                "color" => 3447003, // Couleur bleue
                "fields" => [
                    [
                        "name" => "🔮 • Personnage :",
                        "value" => "@" . $nom . " " . $prenom,
                        "inline" => false
                    ],
                    [
                        "name" => "📋 • — Nom & Prénom :",
                        "value" => $nom . " " . $prenom,
                        "inline" => false
                    ],
                    [
                        "name" => "⏱️ • — Métier :",
                        "value" => $metier_grade,
                        "inline" => false
                    ],
                    [
                        "name" => "🏠 • — Résidence :",
                        "value" => $lieu_residence,
                        "inline" => false
                    ],
                    [
                        "name" => "🎭 • Demande de rôle particulier :",
                        "value" => $role_particulier,
                        "inline" => false
                    ],
                    [
                        "name" => "📝 • Test RP :",
                        "value" => $test_rp,
                        "inline" => false
                    ]
                ]
            ]
        ]
    ];

    // Si une image est présente, ajoute-la à l'embed
    if ($image) {
        $domaine = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $chemin_image = $domaine . '/' . $image;
        $embed["embeds"][0]["image"] = [
            "url" => $chemin_image
        ];
    }

    // URL du webhook Discord
    $webhookUrl = 'https://discord.com/api/webhooks/1355297285025562826/Wd9zrBM1RQS9FOVj2aTDzeb57EJK7Vli-HZx5xvWhyQnC3UiMfCYeHELKXRLX6WcgBYG';

    // Retourne une page HTML avec JavaScript pour envoyer le webhook
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Fiche enregistrée</title>
        <meta charset="UTF-8">
    </head>
    <body>
        <h1>Fiche enregistrée avec succès!</h1>
        <p>Envoi des données vers Discord en cours...</p>
        <div id="status">En attente...</div>
        
        <script>
        // Données de l\'embed
        const webhookUrl = "' . $webhookUrl . '";
        const embedData = ' . json_encode($embed) . ';
        
        // Fonction pour envoyer les données au webhook Discord
        async function sendToDiscord() {
            try {
                const response = await fetch(webhookUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(embedData)
                });
                
                if (response.ok) {
                    document.getElementById("status").innerHTML = 
                        "<p style=\"color: green;\">✓ Données envoyées avec succès vers Discord!</p>" +
                        "<p>Vous pouvez maintenant fermer cette page.</p>";
                } else {
                    document.getElementById("status").innerHTML = 
                        "<p style=\"color: red;\">❌ Erreur lors de l\'envoi: " + response.status + " " + response.statusText + "</p>";
                    console.error("Détails de l\'erreur:", await response.text());
                }
            } catch (error) {
                document.getElementById("status").innerHTML = 
                    "<p style=\"color: red;\">❌ Erreur lors de l\'envoi: " + error.message + "</p>";
                console.error("Erreur complète:", error);
            }
        }
        
        // Exécuter l\'envoi après chargement de la page
        window.onload = sendToDiscord;
        </script>
    </body>
    </html>';

} else {
    // Si la méthode n'est pas POST, afficher une erreur
    echo "Erreur: Veuillez soumettre le formulaire.";
}
?>
