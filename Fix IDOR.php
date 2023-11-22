#Recommendations:
    # * Implement proper authentication and authorization mechanisms to ensure that users can only access their own data.
    # * Instead of relying on the 'id' parameter directly, validate the user's identity and authorize the access based on their privileges.
    # * Use parameterized queries or prepared statements to prevent SQL injection attacks.
<?php

require_once('../_helpers/strip.php');

// Assume the user is authenticated and their ID is known (Authentication and User Identification)
$user_id = 1; #In a real-world scenario, you would have a proper authentication mechanism that identifies the currently logged-in user.
              # Here, we're assuming that the user is authenticated, and their ID is known.


// Create a new SQLite3 database connection
$db = new SQLite3('test.db');

$id = $_GET['id'];

if (strlen($id) > 0) 
{
  // View a particular secret, checking authorization
  $query = $db->prepare('SELECT * FROM secrets WHERE id = :id AND user_id = :user_id');

  #Parameter binding ensures that user-supplied values are treated as data and not executable code. 
  #It also helps prevent SQL injection by handling the escaping and quoting of values.
  $query->bindValue(':id', $id, SQLITE3_INTEGER);
  $query->bindValue(':user_id', $user_id, SQLITE3_INTEGER);

  #The prepared query is executed, and the result is stored in the $result variable.
  $result = $query->execute();

  #Fetching and Displaying Secrets:
  while ($row = $result->fetchArray()) 
  {
    echo 'Secret: ' . $row['secret'];
  }

  echo '<br /><br /><a href="/">Go back</a>';


} 

else
{
  // View all the user's secrets
  $query = $db->prepare('SELECT * FROM secrets WHERE user_id = :user_id');
  $query->bindValue(':user_id', $user_id, SQLITE3_INTEGER);

  $result = $query->execute();

  echo '<strong>Your secrets</strong><br /><br />';

  while ($row = $result->fetchArray()) 
  {
    echo '<a href="/?id=' . $row['id'] . '">#' . $row['id'] . '</a><br />';
  }

}

// Close the database connection
$db->close();
