# CodeAlpha_Secure_Coding_Review

Task3 Summary: Completed a thorough Secure Coding Review for an application written in PHP. Identified and assessed security vulnerabilities(IDOR) through manual code review. Provided actionable recommendations to strengthen overall secure coding practices. 
Join us in the mission to create a secure online environment for everyone! 🌐💙

Let's find out together what an IDOR is?

IDOR stands for Insecure Direct Object Reference and is a type of access control vulnerability.
This type of vulnerability can occur when an application provides direct access to objects based on user-supplied input, and the user can manipulate that input to access unauthorized data

# Vulnerable Code:
The code retrieves a secret based on the provided "id" parameter without checking if the user is authorized to access that secret. 
An attacker can manipulate the id parameter to view secrets belonging to other users.

````
PHP CODE
<?php

require_once('../_helpers/strip.php');

// this database contains a table with 5 rows
$db = new SQLite3('test.db');

$id = $_GET['id'];

if (strlen($id) > 0) 
{
  // view a particular secret
  //
  // As can be seen in the code, the overview page only selects rows
  // from the secrets table WHERE user_id = 1. However, the query
  // below does not have a similar clause OR any kind of authorization
  // check to make sure that the user is authorized to see secret.
  // This means any ID can be passed in the ?id= parameter and be
  // used to read any secret from the table.
  $query = $db->query('select * from secrets where id = ' . (int)$id);

  // In summary, this loop is used to iterate through the results of the SQL query, and for each row, it prints the content of the 'secret' column. 
  // This is typically used to display the secrets retrieved from the database to the user.
  while ($row = $query->fetchArray())
  {
    echo 'Secret: ' . $row['secret'];
  }

  echo '<br /><br /><a href="/">Go back</a>';

} 

else 
{
  // view all the user's secrets (WHERE user_id = 1)
  $query = $db->query('select * from secrets where user_id = 1');

  //this line of code is echoing the HTML markup for a heading that says "Your secrets" in bold, followed by two line breaks.
  echo '<strong>Your secrets</strong><br /><br />';

 
  while ($row = $query->fetchArray()) 
  {
    echo '<a href="/?id=' . $row['id'] . '">#' . $row['id'] . '</a><br />';
  }
  
}

````
# Here is our database 'test.db' as an example:
![new secrets table](https://github.com/MRKING20/CodeAlpha_Secure_Coding_Review/assets/64786452/7b3f7ef0-e112-47ee-a9d0-46e38b8e02af)

  

# Certainly! Let's consider some examples of URLs and potential exploitation scenarios:

1- View All User Secrets: 

    * URL: 'http://example.com/view_secret.php'
    * Exploitation: This URL would display all secrets for the authenticated user (assuming proper authentication logic is implemented).
2- View Specific Secret (ID=1):

    * URL: 'http://example.com/view_secret.php?id=1'
    * Exploitation: If the user is authenticated, they should see the details of their own secret with ID 1.
3- View Specific Secret (ID=2):  

     * URL: 'http://example.com/view_secret.php?id=2'
     * Exploitation: If the user is authenticated, they should see the details of their own secret with ID 2.

4- Attempt to View Another User's Secret (ID=3):
    
    * URL: 'http://example.com/view_secret.php?id=3'
    * Exploitation: Without proper authorization checks, an attacker might attempt to view another user's secret. In the fixed code, this should be prevented.

Exploitation would occur if there are vulnerabilities in the code that allow unauthorized access to secrets. For example, if there were no checks on the user's ID when fetching a specific secret ('SELECT * FROM secrets WHERE id = :id AND user_id = :user_id'), an attacker could manipulate the id parameter to view secrets belonging to other users (Insecure Direct Object References).


# Recommendations to fix this IDOR:
     * Implement proper authentication and authorization mechanisms to ensure that users can only access their own data.
     * Instead of relying on the 'id' parameter directly, validate the user's identity and authorize the access based on their privileges.
     * Use parameterized queries or prepared statements to prevent SQL injection attacks.
#  Fix the vulnerability:

````
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
````

# THE END:
I hope this repository helped you <3 


  
