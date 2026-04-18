/**
 * ============================================================
 *  DakarTech-Hack — Configuration API (XAMPP / htdocs)
 * ============================================================
 *
 *  Chemin de déploiement : C:\xampp\htdocs\ATTENTION04
 *  URL back-end cible    : http://localhost/ATTENTION04/BACK-END/public
 *
 *  Incluez CE fichier en premier dans le <head> de chaque page :
 *  <script src="api-config.js"></script>
 * ============================================================
 */
(function () {
    'use strict';

    // ── 1. Surcharge manuelle (si besoin d'un autre hôte/port) ─────────────
    // Décommentez et ajustez si votre back-end est sur un port différent :
    // window.API_URL_OVERRIDE = 'http://localhost:8000';
    // ───────────────────────────────────────────────────────────────────────

    function detectApiBaseUrl() {
        // Priorité 1 : surcharge manuelle
        if (window.API_URL_OVERRIDE) return window.API_URL_OVERRIDE;

        var origin = window.location.origin;   // ex: http://localhost
        var pathname = window.location.pathname; // ex: /ATTENTION04/FRONT-END/forum_login.html

        // Priorité 2 : chemin XAMPP standard — /ATTENTION04/FRONT-END/
        //  → donne http://localhost/ATTENTION04/BACK-END/public
        var matchXampp = pathname.match(/^(\/ATTENTION04)\//i);
        if (matchXampp) {
            return origin + matchXampp[1] + '/BACK-END/public';
        }

        // Priorité 3 : chemin générique contenant /FRONT-END/ (autre nom de projet)
        var matchGeneric = pathname.match(/^(.*\/)FRONT-END\//i);
        if (matchGeneric) {
            var base = matchGeneric[1].replace(/\/$/, '');
            return origin + base + '/BACK-END/public';
        }

        // Priorité 4 : fallback — même hôte, sous-dossier BACK-END/public à la racine
        return origin + '/ATTENTION04/BACK-END/public';
    }

    window.API = detectApiBaseUrl();

    // ── Helpers partagés ────────────────────────────────────────────────────

    /** Headers HTTP : JSON + Bearer token si disponible */
    window.apiHeaders = function (withAuth) {
        var h = { 'Content-Type': 'application/json' };
        var token = localStorage.getItem('forumToken');
        if (token && withAuth !== false) {
            h['Authorization'] = 'Bearer ' + token;
        }
        return h;
    };

    /** Infos de l'utilisateur connecté (depuis localStorage), ou null */
    window.currentUser = function () {
        var token = localStorage.getItem('forumToken');
        if (!token) return null;
        return {
            token: token,
            id: localStorage.getItem('forumUserId') || '',
            username: localStorage.getItem('forumUsername') || '',
            email: localStorage.getItem('forumEmail') || '',
            role: localStorage.getItem('forumRole') || '',
            avatar: localStorage.getItem('forumAvatar') || '',
        };
    };

    /** Redirige vers le login si non connecté ou rôle insuffisant */
    window.requireAuth = function (allowedRoles) {
        var u = window.currentUser();
        if (!u) { 
            sessionStorage.setItem('logoutReason', 'Authentification requise.');
            window.location.href = 'forum_login.html'; 
            return false; 
        }
        if (allowedRoles && !allowedRoles.includes(u.role)) {
            sessionStorage.setItem('logoutReason', 'Accès refusé : privilèges insuffisants.');
            window.location.href = 'forum_login.html'; 
            return false;
        }
        return true;
    };

    /** Avatar par défaut via DiceBear */
    window.defaultAvatar = function (seed) {
        return 'https://api.dicebear.com/7.x/lorelei/svg?seed=' + encodeURIComponent(seed || 'user');
    };

    // ── Log console (dev) ───────────────────────────────────────────────────
    console.info('[DakarTech-Hack] API →', window.API);
})();
