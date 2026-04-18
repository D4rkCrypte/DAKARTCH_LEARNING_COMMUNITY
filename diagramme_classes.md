# Diagramme de Classes du Site

Le backend étant majoritairement procédural sous PHP (avec des fonctions simples et une connexion PDO), la logique applicative repose sur le modèle de la base de données relationnelle. Le diagramme de classes suivant modélise le "Modèle de Données" (Entités) déduit des tables SQL du site :

```mermaid
classDiagram
    class User {
        +BIGINT id
        +VARCHAR username
        +VARCHAR email
        +VARCHAR password_hash
        +ENUM role
        +VARCHAR avatar_url
        +TIMESTAMP created_at
        +TIMESTAMP updated_at
    }

    class UserToken {
        +BIGINT id
        +BIGINT user_id
        +CHAR token
        +DATETIME expires_at
        +TIMESTAMP created_at
    }

    class ContactMessage {
        +BIGINT id
        +VARCHAR full_name
        +VARCHAR email
        +VARCHAR subject
        +TEXT message
        +TIMESTAMP created_at
    }

    class TeamMember {
        +BIGINT id
        +ENUM team
        +VARCHAR pseudo
        +VARCHAR avatar_url
        +TINYINT is_captain
        +TIMESTAMP created_at
    }

    class ForumTopic {
        +BIGINT id
        +BIGINT author_id
        +VARCHAR title
        +TEXT content
        +VARCHAR category
        +VARCHAR author_name
        +VARCHAR author_email
        +TIMESTAMP created_at
    }

    class ForumReply {
        +BIGINT id
        +BIGINT topic_id
        +BIGINT author_id
        +TEXT content
        +VARCHAR author_name
        +VARCHAR author_email
        +TIMESTAMP created_at
    }

    class NewsArticle {
        +BIGINT id
        +VARCHAR title
        +TEXT content
        +VARCHAR image_url
        +VARCHAR author
        +TIMESTAMP published_at
    }

    class CtfChallenge {
        +BIGINT id
        +VARCHAR title
        +TEXT description
        +VARCHAR category
        +INT points
        +VARCHAR flag
        +TIMESTAMP created_at
    }

    class CtfSolve {
        +BIGINT id
        +BIGINT user_id
        +BIGINT challenge_id
        +TIMESTAMP solved_at
    }

    class ChatMessage {
        +BIGINT id
        +BIGINT user_id
        +BIGINT parent_id
        +TEXT message
        +INT likes_count
        +TIMESTAMP created_at
    }

    User "1" -- "*" UserToken : auth
    User "1" -- "*" ForumTopic : authors
    User "1" -- "*" ForumReply : authors
    User "1" -- "*" CtfSolve : achieves
    User "1" -- "*" ChatMessage : sends
    
    ForumTopic "1" -- "*" ForumReply : contains
    CtfChallenge "1" -- "*" CtfSolve : solved_by
    ChatMessage "1" -- "*" ChatMessage : replies_to
```
