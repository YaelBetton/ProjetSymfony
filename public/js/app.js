// Scripts d'ergonomie / accessibilité communs à toutes les pages.
document.addEventListener('DOMContentLoaded', () => {
    // ---------- Menu principal repliable sur petit écran (bouton « Menu ») ----------
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.getElementById(toggle?.getAttribute('aria-controls') ?? '');

    if (toggle && nav) {
        const setOpen = (open) => {
            toggle.setAttribute('aria-expanded', String(open));
            nav.classList.toggle('is-open', open);
        };

        toggle.addEventListener('click', () => {
            setOpen(toggle.getAttribute('aria-expanded') !== 'true');
        });

        // Échap referme le menu et rend le focus au bouton
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
                setOpen(false);
                toggle.focus();
            }
        });
    }

    // ---------- Formulaire renvoyé avec des erreurs : focus sur le premier champ invalide ----------
    const firstInvalid = document.querySelector('form [aria-invalid="true"]');
    if (firstInvalid) {
        firstInvalid.focus();
    }
});
