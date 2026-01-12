/**
 * Security Script - Protect Source Code
 * Note: These are deterrents and not 100% foolproof.
 */

(function() {
    // 1. Disable Right-Click Context Menu
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    // 2. Disable Common Keyboard Shortcuts for DevTools
    document.addEventListener('keydown', function(e) {
        // F12
        if (e.keyCode === 123) {
            e.preventDefault();
            return false;
        }
        
        // Ctrl + Shift + I (Inspect)
        if (e.ctrlKey && e.shiftKey && e.keyCode === 73) {
            e.preventDefault();
            return false;
        }

        // Ctrl + Shift + J (Console)
        if (e.ctrlKey && e.shiftKey && e.keyCode === 74) {
            e.preventDefault();
            return false;
        }

        // Ctrl + Shift + C (Element Selector)
        if (e.ctrlKey && e.shiftKey && e.keyCode === 67) {
            e.preventDefault();
            return false;
        }

        // Ctrl + U (View Source)
        if (e.ctrlKey && e.keyCode === 85) {
            e.preventDefault();
            return false;
        }

        // Ctrl + S (Save Page)
        if (e.ctrlKey && e.keyCode === 83) {
            e.preventDefault();
            return false;
        }
    });

    // 3. Debugger Anti-Inspect Trap
    // This will pause the script constantly if DevTools are open
    setInterval(function() {
        (function() {
            return false;
        }['constructor']('debugger')['call']());
    }, 1000);

    // 4. Clear console occasionally
    setInterval(function() {
        console.clear();
        console.log("%c¡ALTO!", "color: red; font-size: 50px; font-weight: bold; -webkit-text-stroke: 1px black;");
        console.log("%cEl acceso a la consola está restringido por seguridad.", "font-size: 18px; color: #334155;");
    }, 2000);
})();
