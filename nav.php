<!DOCTYPE html>
<html lang="en">
<head>
    <style>
       /* Add styles for smoother dropdown animation */
       .dropdown-menu {
            display: none; /* Initially hide the dropdown */
            opacity: 0; /* Initial opacity for transition */
            transition: opacity 0.3s ease, transform 0.3s ease; /* Transition for opacity and transform */
            transform: translateY(-10px); /* Slight upward shift for animation */
            position: absolute; /* Position it absolutely */
            z-index: 1000; /* Ensure it's above other elements */
        }

        .dropdown.show .dropdown-menu {
            display: block; /* Show dropdown on click */
            opacity: 1; /* Fully opaque when visible */
            transform: translateY(0); /* Reset position */
        }

        #navbar {
        padding: 10px;
        transition: top 0.3s; /* Smooth transition for top property */
    }

    .hidden {
        top: -70px; /* Adjust based on navbar height */
    }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light" id="navbar">
    <a class="navbar-brand" href="">Monitor Panel</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>    
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="onlineDropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    Online ONUs
                </a>
                <div class="dropdown-menu" aria-labelledby="onlineDropdown">
                    <a class="dropdown-item" href="../fontend/pon_1.php">Pon Port 1</a>
                    <a class="dropdown-item" href="../fontend/pon_2.php">Pon Port 2</a>
                    <a class="dropdown-item" href="../fontend/pon_3.php">Pon Port 3</a>
                    <a class="dropdown-item" href="../fontend/pon_4.php">Pon Port 4</a>
                    <a class="dropdown-item" href="../fontend/pon_all.php">All Pon Port</a>
                </div>
            </li>    
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'offline.php') ? 'active' : ''; ?>" href="../fontend/offline.php">Offline ONUs</a>
            </li>    
        </ul>
    </div>
</nav>

<script>
   $(document).ready(function() {
        // Add click event to toggle dropdown
        $('#onlineDropdown').on('click', function(event) {
            event.preventDefault(); // Prevent default link behavior
            var $dropdown = $(this).parent();
            var $menu = $(this).next('.dropdown-menu');

            if ($dropdown.hasClass('show')) {
                // Animate and hide the dropdown menu
                $menu.css('transition', 'opacity 0.3s ease, transform 0.3s ease');
                $menu.css({'opacity': 0, 'transform': 'translateY(-10px)'});
                
                // Delay removing the show class until the animation is complete
                setTimeout(function() {
                    $menu.hide(); // Hide the dropdown menu
                    $dropdown.removeClass('show'); // Remove the show class
                }, 300); // Match this to the CSS transition duration
            } else {
                $dropdown.addClass('show'); // Add show class
                $menu.show().css({'opacity': 1, 'transform': 'translateY(0)'}); // Show dropdown with opacity
            }
        });

        // Close the dropdown if clicked outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                var $menu = $('.dropdown-menu');
                $menu.css('transition', 'opacity 0.3s ease, transform 0.3s ease');
                $menu.css({'opacity': 0, 'transform': 'translateY(-10px)'});

                // Delay closing the dropdown until the animation is complete
                setTimeout(function() {
                    $menu.hide(); // Hide the dropdown menu
                    $('.dropdown').removeClass('show'); // Remove the show class
                }, 300); // Match this to the CSS transition duration
            }
        });
    });
</script>
</body>
</html>
