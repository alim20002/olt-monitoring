
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/scripts.js"></script>
    <link rel="stylesheet" href="/css/style.css">
    

    <style>
    
    #navbar {
        padding: 10px;
        transition: top 0.3s; /* Smooth transition for top property */
    }

    .hidden {
        top: -70px; /* Adjust based on navbar height */
    }
</style>
<title>Users Monitor Panel</title>

<script>
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
</script>


<script>
    // Check if the browser supports the history API
    if (window.history && window.history.pushState) {
        window.history.pushState(null, "", "/"); // This updates the URL without reloading
    }
</script>


<!-- fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">



<nav class="navbar navbar-expand-lg navbar-light bg-light" id="navbar">
    <a class="navbar-brand" href="http://ibl.free.nf/">Monitor Panel</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>    
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'online.php') ? 'active' : ''; ?>" href="../fontend/online.php">Online ONUs</a>
            </li>    
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'offline.php') ? 'active' : ''; ?>" href="../fontend/offline.php">Offline ONUs</a>
            </li>    
                        
        </ul>
    </div>
</nav>