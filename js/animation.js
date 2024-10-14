jsFiles.forEach(file => {
    const script = document.createElement('script');
    script.src = file;
    script.async = false; // To maintain execution order
    document.head.appendChild(script);
});



//Animatin

$(document).ready(function() {
    // Add animation on page load
    $('.fade-in').css('opacity', 1);
});



// scrolling

let lastScrollTop = 0; // Store the last scroll position
    const navbar = document.getElementById('navbar');

    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;

        if (currentScroll > lastScrollTop) {
            // Scrolling down, hide the navbar
            navbar.classList.add('hidden');
        } else {
            // Scrolling up, show the navbar
            navbar.classList.remove('hidden');
        }

        lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; // For Mobile or negative scrolling
    });


