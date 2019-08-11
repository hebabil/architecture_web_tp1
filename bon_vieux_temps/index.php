<?php

session_start();

$pdo = new \PDO('mysql:host=127.0.0.1;port=3306;dbname=architecture_web_tp1;charset=utf8;', 'root', 'root');

if (isset($_GET['logout'])) {
    $_SESSION['user_id'] = null;
}

function estConnecte()
{
    return $_SESSION['user_id'] != null;
}

$erreurConnexion = null;
if (isset($_POST['connexion'])) {
    $requete = 'select * from users where email = "'.$_POST['email'].'"';
    $data = $pdo->query($requete)->fetch();

    if (!$data) {
        $erreurConnexion = 'Utilisateur non trouvé';

    } else if ($data['password'] != $_POST['password']) {
        $erreurConnexion = 'Mot de passe invalide';
    } else {
        $_SESSION['user_id'] = $data['id'];
    }
}

$erreurCreation = null;
if (isset($_POST['commenter'])) {
    $insert = $pdo->exec('Insert into comments (task_id, created_by, created_at, comment) values ('.$_POST['todo_id'].', '.$_SESSION['user_id'].', NOW(), "'.$_POST['comment'].'")');
    if (!$insert) {
        $erreurCreation = 'Erreur lors de l\'ajout du commentaire.';
    }
}

if (isset($_POST['ajouter'])) {
    $insert = $pdo->exec('Insert into todos (created_by, created_at, title, description) values ('.$_SESSION['user_id'].', NOW(), "'.$_POST['title'].'", "'.$_POST['description'].'" )');
    if (!$insert) {
        $erreurCreation = 'Erreur lors de l\'ajout de la tâche.';
    }
}


if (!estConnecte()) {
    ?>
    <html>
        <body>
            <h1>Connectez vous pour accéder à la todo list !</h1>
            <?php if (!empty($erreurConnexion)) { ?>
                <p style="color: red"><?= $erreurConnexion; ?></p>
            <?php } ?>
            <form method="post" action="index.php">
                <p><label>Votre email&nbsp;:</label><input type="text" name="email" /></p>
                <p><label>Votre mot de passe&nbsp;:</label><input type="password" name="password" /></p>
                <p></p><input type="submit" name="connexion" value="Se connecter" /></p>
            </form>
        </body>
    </html>
    <?php
} else {
  ?>
    <html>
        <body>
            <h1>Todo list partagée</h1>
            <?php if (!empty($erreurCreation)) { ?>
                <p style="font-weight: bold; color: red;"><?= $erreurCreation; ?></p>
            <?php } ?>
            <ul>
            <?php
                foreach ($pdo->query('select todos.*, writer.email as writer_email  from todos left join users writer on todos.created_by = writer.id order by todos.id desc')->fetchAll() as $todo) { ?>
                 <li><a href="#" data-purpose="link-todo" data-todo-id="<?= $todo['id'] ?>"><?= $todo['title'] ?></a>
                    <div style="display: none" data-purpose="description-todo" data-todo-id="<?= $todo['id'] ?>">
                        <p>Créé par <?= $todo['writer_email']; ?> le <?= $todo['created_at']; ?></p>

                        <p style="text-decoration: underline">Commentaires :</p>
                        <ul style="list-style-type: none">

                        <?php foreach ($pdo->query('select comments.*, writer.email as writer_email from comments left join users writer on comments.created_by = writer.id where task_id = '.$todo['id'])->fetchAll() as $comment) { ?>
                            <li style="border: 1px solid #ddd; margin: 10px; padding: 10px">
                                <?= $comment['comment']; ?><br />
                                Posté le <?= $comment['created_at']; ?> par <?= $comment['writer_email']; ?>
                            </li>
                        <?php
                        }?>
                        </ul>
                        <div style="display: block; margin-left: 50px">
                            <form method="post" action="index.php" name="addComment">
                                <p></p><input type="hidden" name="todo_id" value="<?= $todo['id']; ?>" /></p>
                                <p></p><textarea name="comment" style="width: calc(100% - 10px)"></textarea></p>
                                <input type="submit" name="commenter" value="Ajouter un commentaire" />
                            </form>
                        </div>

                    </div>
                 </li>
            <?php
                }
            ?>
            </ul>

            <h2>Ajouter une tâche</h2>
            <form method="post" action="index.php">
                <p><label>Titre&nbsp;:</label><input type="text" name="title" /></p>
                <p><label>Description&nbsp;:</label><textarea name="description"></textarea></p>
                <input type="submit" name="ajouter" value="Ajouter une tâche" />
            </form>

            <p><a href="index.php?logout">Se déconnecter</a></p>
        </body>
            <script
                src="https://code.jquery.com/jquery-3.4.1.js"
                integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="
                crossorigin="anonymous"></script>

        <script>
            $('[data-purpose="link-todo"]').click(function (event) {
                event.preventDefault();
                var todoId = $(event.currentTarget).attr('data-todo-id');
                var todoDesc = $('[data-purpose="description-todo"][data-todo-id="'+todoId+'"]');
                todoDesc.toggle();

            });
        </script>

    </html>
    <?php
}