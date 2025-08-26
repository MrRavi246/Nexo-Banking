// const USER = {
//   username: "admin",
//   password: "1234",
// };

// function login(e) {
//   const enteredUser = document.getElementById("account_number").value;
//   const enteredPass = document.getElementById("password").value;
//   console.log("enteredUser:", enteredUser);
//   if (enteredUser === USER.username && enteredPass === USER.password) {
//     e.preventDefault();
//     localStorage.clear();
//     localStorage.setItem("isLoggedIn", "true");
//     localStorage.setItem("account_number", enteredUser);

//     window.location.href = "../index.html";
//   } else if (
//     enteredUser === "" ||
//     enteredPass === "" ||
//     enteredUser !== USER.account_number ||
//     enteredPass !== USER.password
//   ) {
//     e.preventDefault();
//     const errorEl = document.createElement("p");
//     errorEl.innerText = "Invalid account_number or password.";
//     errorEl.style.color = "red";
//     document.querySelector(".right-side").appendChild(errorEl);
//   } else {
//     //   errorEl.innerText = "Invalid account_number or password.";
//     console.error("Error");
//     e.preventDefault();
//     const errorEl = document.createElement("p");
//     errorEl.innerText = "Invalid account_number or password.";
//     errorEl.style.color = "red";
//     document.querySelector(".right-side").appendChild(errorEl);
//   }
// }
