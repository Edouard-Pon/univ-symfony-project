Edouard Pon  
Yehor KUZMENKO

Ce projet est destiné à la génération de tests de type QCM à l'aide de l'intelligence artificielle.

## Installation

1. Cloner le projet
2. Instalation des API's
   Pour lancer le projet, vous devrez utiliser plusieurs API responsables de différentes fonctionnalités. La première API est chargée de générer les tests.

    ⚠ ATTENTION : Étant donné qu'il s'agit d'une intelligence artificielle, assurez-vous d'avoir suffisamment de mémoire vidéo pour exécuter le modèle. On a utilisé le modèle 14B avec 8 Go de mémoire vidéo.

    Ce service repose sur Ollama, et pour l'installer, exécutez les commandes suivantes :

   Veuillez visiter le lien suivant pour plus d'informations sur Ollama API [Ollama Api Doc](https://github.com/ollama/ollama/blob/main/docs/api.md)

   Veuiilez visiter le lien suivant pour plus d'informations sur Ollama Docker [Ollama Docker](https://hub.docker.com/r/ollama/ollama)
    
   GPU:
   ```sh
   docker run -d --gpus=all -v ollama:/root/.ollama -p 11434:11434 --name ollama ollama/ollama
   ```

   CPU:
    ```sh 
    docker run -d -v ollama:/root/.ollama -p 11434:11434 --name ollama ollama/ollama
    ```
   
   Pour pull l'image:
    ```sh
   docker exec -it ollama ollama pull deepseek-r1:14b
   ```

    OU (si vous avez déjà téléchargé l'image)

    ```sh
    docker start <container_name>
    ```

    Après avoir lancé le conteneur avec Ollama, vous devez également télécharger et exécuter deux autres API responsables des fonctionnalités "divertissement" aux liens suivants :

    🔹 API 1 – [symf-api](https://github.com/YehorKuzmenko/symf-api )

    👉 Cette API correspond à la variable RANDOM_NUMBER_API_URL dans le fichier .env.

    🔹 API 2 – [univ-symfony-api-project](https://github.com/Edouard-Pon/univ-symfony-api-project)

    👉 Cette API correspond à la variable MOTIVATION_API_URL dans le fichier .env.

    Assurez-vous qu'elles sont correctement configurées et en cours d'exécution avant de tester le projet principal. 🚀


3. Setup

   Après l'installation et le lancement de tous les composants, vous devez exécuter la commande du Makefile qui installe tous les éléments essentiels.

    🚨 Assurez-vous également d'avoir renseigné les informations de votre base de données dans le fichier .env.

    Pour lancer l'installation, utilisez la commande suivante :
    ```sh
    make setup-dev
    ```
4. Lancer le projet

   Pour lancer le projet, vous devez exécuter la commande suivante :
    ```sh
    symfony serve
    ```
5. Accéder à l'application

   Par défaut, si vous avez utilisé la commande `make setup-dev`, un utilisateur est déjà créé dans la base de données. Vous pouvez l'utiliser pour vous connecter avec les identifiants suivants :

    📧 Email : admin@exemple.com

    🔑 Mot de passe : password123

    Cependant, vous avez également la possibilité de créer un nouveau compte en vous inscrivant. 🚀
    
