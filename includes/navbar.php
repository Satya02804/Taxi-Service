<style>
    .navbar {
        background-color: rgba(0, 0, 0, 0.8);
        padding: 15px 30px;
        border-radius: 0 0 50px 50px;
        margin: 0 20px;
        transition: all 0.3s ease;
    }

    .navbar.scrolled {
        margin: 10px 20px;
        border-radius: 50px;
        background-color: rgba(0, 0, 0, 0.9) !important;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .navbar-brand {
        color: #ffc107 !important;
        font-size: 24px;
        font-weight: bold;
    }

    .nav-link {
        color: white !important;
        margin: 0 10px;
        transition: color 0.3s ease;
    }

    .nav-link:hover {
        color: #ffc107 !important;
    }
</style>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">Taxi Service</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['admin_id'])): ?>
                    <!-- Admin Navigation -->
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="drivers.php">Drivers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php elseif(isset($_SESSION['driver_id'])): ?>
                    <!-- Driver Navigation -->
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php elseif(isset($_SESSION['user_id'])): ?>
                    <!-- User Navigation -->
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="book.php">Book Ride</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_rides.php">My Rides</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($_SESSION['admin_id']) ? '../' : ''; ?>taxi-gallery.php">Our Fleet</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
</script> 