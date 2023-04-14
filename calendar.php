<!DOCTYPE html>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<html>

<head>
    <title>Booking Calendar</title>
    <style>
        header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid black;
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

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: dimgrey;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            margin: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            border-radius: 5px;
        }

        th,
        td {
            text-align: center;
            padding: 10px;
            border: 1px solid black;
        }

        th {
            background-color: darkgray;
            color: white;
        }

        td {
            background-color: white;
        }

        td:hover {
            background-color: lightgray;
        }
    </style>
</head>

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

    <?php $currentmonthheader = date('F');
    $currentyearheader = date('Y');
    echo "<h1>Booking Calendar for $currentmonthheader $currentyearheader</h1>"; ?>

    <?php
    $host_name = 'db5012649972.hosting-data.io';
    $database = 'dbs10629461';
    $user_name = 'dbu5421620';
    $password = 'Password';  //I got rid of the password here as it is a personal password.

    $conn = new mysqli($host_name, $user_name, $password, $database);

    if ($conn->connect_error) {
        die('<p>Failed to connect to MySQL: ' . $conn->connect_error . '</p>');
    } else {
        //echo '<p>Connection to MySQL server successfully established.</p>';
    }

    if (isset($_POST['submit'])) {
        // Get the form data
        $date = $_POST['date'];
        $time = $_POST['time'];
        $name = $_POST['name'];

        // Check if the date and time have already been booked
        $sql = "SELECT COUNT(*) AS count FROM bookings WHERE date = '$date' AND time = '$time'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $count = $row['count'];
        if ($count > 0) {
            echo "This date and time have already been booked. Please choose another date and time.";
        } else {
            // Insert the booking into the database
            $sql = "INSERT INTO bookings (date, time, name) VALUES ('$date', '$time', '$name')";
            if ($conn->query($sql) === TRUE) {
                echo "Booking created successfully";
            } else {
                echo "Error creating booking: " . $conn->error;
            }
        }
    }

    // Get the bookings for the current month
    $month = date('m');
    $year = date('Y');
    $num_days = date('t', strtotime("$year-$month-01"));
    $bookings = array();
    $sql = "SELECT * FROM bookings WHERE date >= '$year-$month-01' AND date <= '$year-$month-$num_days'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $date = $row['date'];
            $time = $row['time'];
            $name = $row['name'];

            if (!isset($bookings[$date])) {
                $bookings[$date] = array();
            }
            $bookings[$date][$time] = $name;
        }
    }

    // create the calendar
    echo '<table>';
    echo '<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>';
    $first_day = date('N', strtotime("$year-$month-01"));

    if ($first_day != 7) {
        $first_day += 1;
    } else {
        $first_day = 1;
    }

    $last_day = date('N', strtotime("$year-$month-$num_days"));
    $current_day = 1;
    $week = 1;
    $done = false;

    while (!$done) {
        echo '<tr>';
        for ($i = 1; $i <= 7; $i++) {
            if ($current_day == 1 && $i < $first_day) {
                echo '<td></td>';
            } else if ($current_day > $num_days) {
                echo '<td></td>';
                $done = true;
            } else {
                $date = "$year-$month-$current_day";
                $bookings_for_day = isset($bookings[$date]) ? $bookings[$date] : array();
                $booking_slots = array('9:00', '9:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '1:00', '1:30', '2:00', '2:30', '3:00', '3:30', '4:00', '4:30', '5:00');
                echo '<td>';
                echo '<strong>' . $current_day . '</strong><br>';

                foreach ($booking_slots as $slot) {
                    if (isset($bookings_for_day[$slot])) {
                        echo $slot . ': ' . $bookings_for_day[$slot] . '<br>';
                    } else {
                        echo $slot . ': ';
                        echo '<form method="post" action="book.php">';
                        echo '<input type="hidden" name="date" value="' . $date . '">';
                        echo '<input type="hidden" name="time" value="' . $slot . '">';
                        echo '<button type="submit" name="submit">Book</button>';
                        echo '</form><br>';
                    }
                }

                echo '</td>';
                $current_day++;
            }
        }
        echo '</tr>';
    }
    echo '</table>';