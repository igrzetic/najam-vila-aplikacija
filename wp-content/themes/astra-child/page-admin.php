<?php
/**
 * Template Name: Admin Blank Page
 * Description: Blank page without Astra header/footer. Visible only to logged-in admins.
 */

defined('ABSPATH') || exit;

// Access control: only logged-in admins (custom app session)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$login_url = home_url('/login/');

// Handle logout action
if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    if (!empty($_SESSION)) {
        $_SESSION = [];
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    wp_safe_redirect($login_url);
    exit;
}
$is_admin = isset($_SESSION['app_user']) && !empty($_SESSION['app_user']['role']) && $_SESSION['app_user']['role'] === 'admin';
if (!$is_admin) {
    // Redirect non-admins to homepage (adjust if you have a dedicated login slug)
    wp_safe_redirect($login_url ?: home_url('/'));
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class('custom-admin'); ?>>
  <nav class="navbar navbar-expand-lg navbar-mainbg navbar-dark bg-dark">
    <a class="navbar-brand navbar-logo" href="#">Dashboard</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <i class="fas fa-bars text-white"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <div class="hori-selector"><div class="left"></div><div class="right"></div></div>
        <li class="nav-item active">
          <a class="nav-link" href="#users"><i class="fas fa-users"></i>Users</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#properties"><i class="fas fa-home"></i>Properties</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#reservations"><i class="fas fa-calendar-check"></i>Reservations</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#shopping-list"><i class="fas fa-clipboard-list"></i>Shopping List</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#damage-report"><i class="fas fa-exclamation-triangle"></i>Damage report</a>
        </li>
      </ul>
    </div>
  </nav>
  <main id="admin-blank" class="admin-blank" role="main">
    <!-- Users screen: form + table -->
    <section id="users" class="glass-login users-screen" aria-label="Users">
      <button id="toggle-users-table" type="button" class="btn-toggle-table" aria-expanded="false">Show Table</button>
      <a id="logout-floating" class="btn-toggle-table btn-logout" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
      <div class="background" aria-hidden="true">
        <div class="shape"></div>
        <div class="shape"></div>
      </div>
      <div class="users-layout">
        <form id="users_form" class="glass-form" action="<?php echo esc_url( get_stylesheet_directory_uri() . '/custom/user_functions/add-user-form.php' ); ?>" method="post" novalidate>
          <h2>Add User</h2>

        <label for="gl-username">Username</label>
        <input type="text" placeholder="Username" id="gl-username" name="username" />

        <label for="gl-password">Password</label>
        <input type="password" placeholder="Password" id="gl-password" name="password" />

        <label for="gl-email">Email</label>
        <input type="email" placeholder="Email" id="gl-email" name="email" />

        <label for="gl-role">Role</label>
        <select id="gl-role" name="role">
          <option value="" disabled selected>-- Select user type --</option>
          <option value="admin">Admin</option>
          <option value="owner">Owner</option>
          <option value="services">Services</option>
        </select>

          <button type="submit" name="add_user_submit">Add User</button>
          <button type="button" name="update_user_submit" style="display:none;">Update User</button>
        </form>

        <div class="users-table">
          <div class="table-scroll">
            <?php
              $users_table_path = get_stylesheet_directory() . '/custom/user_functions/users-table.php';
              if ( file_exists( $users_table_path ) ) {
                $users_table_html = include $users_table_path; // file returns ob_get_clean()
                echo $users_table_html;
              } else {
                echo '<p>users-table.php not found.</p>';
              }
            ?>
          </div>
        </div>
      </div>
    </section>
    <script>
      (function () {
        const btn = document.getElementById('toggle-users-table');
        const section = document.querySelector('#users.glass-login.users-screen');
        if (!btn || !section) return;
        const isShown = () => section.classList.contains('show-table');
        const updateUI = () => {
          btn.textContent = isShown() ? 'Hide Table' : 'Show Table';
          btn.setAttribute('aria-expanded', String(isShown()));
        };
        btn.addEventListener('click', () => {
          section.classList.toggle('show-table');
          updateUI();
        });
        updateUI();
      })();
    </script>
    
    <!-- Properties screen: form + table -->
    <section id="properties" class="glass-login users-screen" aria-label="Properties">
      <button id="toggle-properties-table" type="button" class="btn-toggle-table" aria-expanded="false">Show Table</button>
      <a class="btn-toggle-table btn-logout" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
      <div class="background" aria-hidden="true">
        <div class="shape"></div>
        <div class="shape"></div>
      </div>
      <div class="users-layout">
        <div class="glass-form">
          <?php
            $property_form_path = get_stylesheet_directory() . '/property_functions/rental-property-form.php';
            if ( file_exists( $property_form_path ) ) {
              $property_form_html = include $property_form_path; // file returns ob_get_clean()
              echo $property_form_html;
            } else {
              echo '<p>rental-property-form.php not found.</p>';
            }
          ?>
        </div>

        <div class="users-table">
          <div class="table-scroll">
            <?php
              $properties_table_path = get_stylesheet_directory() . '/property_functions/rental-propertys-table.php';
              if ( file_exists( $properties_table_path ) ) {
                $properties_table_html = include $properties_table_path; // file returns ob_get_clean()
                echo $properties_table_html;
              } else {
                echo '<p>rental-propertys-table.php not found.</p>';
              }
            ?>
          </div>
        </div>
      </div>
    </section>
    <script>
      (function () {
        const btn = document.getElementById('toggle-properties-table');
        const section = document.querySelector('#properties.glass-login.users-screen');
        if (!btn || !section) return;
        const isShown = () => section.classList.contains('show-table');
        const updateUI = () => {
          btn.textContent = isShown() ? 'Hide Table' : 'Show Table';
          btn.setAttribute('aria-expanded', String(isShown()));
        };
        btn.addEventListener('click', () => {
          section.classList.toggle('show-table');
          updateUI();
        });
        updateUI();
      })();
    </script>

    <!-- Reservations screen: form + table -->
     <section id="reservations" class="glass-login users-screen" aria-label="Reservations">
        <button id="toggle-reservations-table" type="button" class="btn-toggle-table" aria-expanded="false">Show Table</button>
        <a id="logout-floating" class="btn-toggle-table btn-logout" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
        <div class="background" aria-hidden="true">
          <div class="shape"></div>
          <div class="shape"></div>
        </div>
        <div class="users-layout">
          <div class="glass-form">
            <?php
              $reservations_form_path = get_stylesheet_directory() . '/custom/reservation_functions/reservations-form.php';
              if ( file_exists( $reservations_form_path ) ) {
                $reservations_form_html = include $reservations_form_path; // file returns ob_get_clean()
                echo $reservations_form_html;
              } else {
                echo '<p>reservations-form.php not found.</p>';
              }
            ?>
          </div>

          <div class="users-table">
            <div class="table-scroll">
              <?php
                $reservations_table_path = get_stylesheet_directory() . '/custom/reservation_functions/reservations-table.php';
                if ( file_exists( $reservations_table_path ) ) {
                  $reservations_table_html = include $reservations_table_path; // file returns ob_get_clean()
                  echo $reservations_table_html;
                } else {
                  echo '<p>reservations-table.php not found.</p>';
                }
              ?>
            </div>
          </div>
        </div>
      </div>
     </section>
     <script>
      (function () {
        const btn = document.getElementById('toggle-reservations-table');
        const section = document.querySelector('#reservations.glass-login.users-screen');
        if (!btn || !section) return;
        const isShown = () => section.classList.contains('show-table');
        const updateUI = () => {
          btn.textContent = isShown() ? 'Hide Table' : 'Show Table';
          btn.setAttribute('aria-expanded', String(isShown()));
        };
        btn.addEventListener('click', () => {
          section.classList.toggle('show-table');
          updateUI();
        });
        updateUI();
      })();
    </script>
  </main>
  <?php wp_footer(); ?>
</body>
</html>
