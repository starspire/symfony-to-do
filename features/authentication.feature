Feature: Authentication
  In order to gain access to the site management area
  As a web user
  I need to be able to login and logout

  Scenario: Succesfully login
    Given there is a user "user" with password "password"
    And I am on "/login"
    When I fill in "Username" with "user"
    And I fill in "Password" with "password"
    And I press "Login"
    Then I should see "Signed in as user"

  Scenario: Failed login
    Given there is a user "user" with password "password"
    And I am on "/login"
    When I fill in "Username" with "user"
    And I fill in "Password" with "wrongpassword"
    And I press "Login"
    Then I should see "Invalid credentials"
    
  Scenario: Logout
    Given I am logged in as a user
    When I follow "Logout"
    Then I should be on "/login"

