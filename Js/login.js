const USER = {
    username: "admin",
    password: "1234"
  };

  function login() {
    const enteredUser = document.getElementById("account_number").value;
    const enteredPass = document.getElementById("password").value;
    // const errorEl = document.getElementById("error");

    if (enteredUser === USER.account_number && enteredPass === USER.password) {
      localStorage.setItem("isLoggedIn", "true");
      localStorage.setItem("account_number", enteredUser);
        console.log("hear")
      window.location.href = "../index.html";
    } else {
    //   errorEl.innerText = "Invalid account_number or password.";
    console.error("Error");
    
    }
  }