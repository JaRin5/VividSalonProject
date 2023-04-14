<html>

<head>
  <title>Vivid Salon</title </head>

  <style>
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: dimgrey;
    }

    nav {
      justify-content: space-between;
      text-align: right;
      margin-right: 200px;
    }

    nav a {
      color: #ffffff;
      font-size: 20px;
      text-decoration: none;
      padding: 10px;
    }

    nav a:hover {
      color: #c3c3c3;
    }

    ul {
      list-style-type: none;
    }

    .error-message {
      color: white;
      font-weight: bold;
      text-align: center;
      font-size: 20px;
      padding: 50px;
    }

    .success-message {
      color: white;
      font-size: 30px;
      text-align: center;
      padding: 50px;

    }

    header {
      background-color: #333;
      color: #fff;
      padding: 20px;
      text-align: center;
      border-bottom: 2px solid black;
    }
  </style>

<body>
  <header>
    <nav>
      <ul>
        <a href="home.html">Home</a>
        <a href="services.html">Services</a>
        <a href="about.html">About Us</a>
        <a href="portfolio.html">Portfolio</a>
        <a href="calendar.php">Book Now</a>
        <a href="contact.html">Contact & Hours</a>
      </ul>
    </nav>
    <h1>Vivid Salon</h1>
  </header>
</body>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['submit'])) {
  $host_name = 'db5012649972.hosting-data.io';
  $database = 'dbs10629461';
  $user_name = 'dbu5421620';
  $password = 'Password';  //I have removed the password here as it is a personal password.

  // Create a connection to the database
  $conn = new mysqli($host_name, $user_name, $password, $database);

  // Check if the connection was successful
  if ($conn->connect_error) {
    die('<p>Failed to connect to MySQL: ' . $conn->connect_error . '</p>');
  }

  // Get the form data
  $date = $_POST['date'];
  $time = $_POST['time'];

  // Check if the date and time have already been booked
  $sql = "SELECT COUNT(*) AS count FROM bookings WHERE date = '$date' AND time = '$time'";
  $result = $conn->query($sql);
  $row = $result->fetch_assoc();
  $count = $row['count'];
  if ($count > 0) {
    echo "<p class='error-message'>This date and time have already been booked. Please choose another date and time.</p>";
  } else {
    // Insert the booking into the database
    $sql = "INSERT INTO bookings (date, time, name) VALUES ('$date', '$time', '')";
    if ($conn->query($sql) === TRUE) {
      echo "<p class='success-message'>Booking created successfully!\nWe have been notified of your appointment on $date at $time, thank you.</p>";

      // Send email using Sendinblue
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => "smtp-relay.sendinblue.com",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{  
                 \"sender\":{  
                    \"name\":\"Vivid Salon\",
                    \"email\":\"ringejs@gmail.com\"
                 },
                 \"to\":[  
                    {  
                       \"email\":\"assain494@gmail.com\",
                       \"name\":\"Appointment\"
                    }
],
                 \"subject\":\Appointment\",
                 \"htmlContent\":\"<html><head></head><body><p>Appointment,</p>An appointment has been scheduled for $date at $time.</p></body></html>\"
              }",
        CURLOPT_HTTPHEADER => array(
          "accept: application/json",
          "api-key: API has been removed",  //The secret API key has been removed from this since it will be hosted public on github
          "content-type: application/json"
        ),
      )
      );

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        echo "Email sent successfully";
      }

    } else {
      echo "Error creating booking: " . $conn->error;
    }
  }

  // Close the database connection
  $conn->close();
}
?>

</html>