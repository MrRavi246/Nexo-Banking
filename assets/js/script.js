

function lenis() {
  // Initialize Lenis
  const lenis = new Lenis({
    autoRaf: true,
    smoothWheel: true,
    lerp: 1,
    smoothTouch: true,
    touchMultiplier: 2,
    duration: 3,
  });

  // Listen for the scroll event and log the event data
  lenis.on("scroll", (e) => {});
}
lenis();

// to remove right click
// document.addEventListener(
//   "contextmenu",
//   function (e) {
//     e.preventDefault();
//   },
//   false
// );

// to remove scrollbar
document.body.style.overflow = "hidden";



function middleware() {
  function authMiddleware(next) {
    const user =
      localStorage.getItem("isLoggedIn") === "true"
        ? {
            account_number: localStorage.getItem("account_number"),
          }
        : null;

    if (user) {
      console.log("User is authenticated:", user);
      mainApp();
      // next();
    } else {
      alert("Unauthorized!");
      window.location.href = "Pages/login.html";
      localStorage.clear();
    }
  }

  function mainApp() {
    console.log("User is allowed, app running...");
  
  }

  authMiddleware(mainApp);
}

// middleware();


document.addEventListener("DOMContentLoaded", (event) => {
  // gsap code here!
  function GSAP() {
    if (window.gsap && window.ScrollTrigger) {
      gsap.registerPlugin(ScrollTrigger, SplitText);

      function loader() {
        
        let split = SplitText.create(".con", { type: "chars" });

        let tl = gsap.timeline();

        tl.to("main", {
          display: "none",
        });

        tl.from(split.chars, {
          duration: 0.8,
          y: 100,
          autoAlpha: 0,
          stagger: 0.2,
        });

        tl.to(split.chars, {
          duration: 0.8,
          y: -100,
          autoAlpha: 0,
          stagger: 0.2,
        });

        tl.to(".loader", {
          duration: 0.5,
          autoAlpha: 0,
          display: "none",
          backgroundColor: `rgba(0, 0, 0, 0.77)`,
        });

        tl.to("main", {
          display: "block",
          delay: -0.7,
        });
      }
      loader();

      function page1() {
        const tl = gsap.timeline({
          scrollTrigger: {
            trigger: ".page1",
            start: "50% 50%",
            end: "250% -50%",
            scrub: 2,
            pin: `.page1`,
          },
        });

        tl.to(
          ".content",
          {
            rotate: -10,
            scale: 0.8,
          },
          "<"
        );
        tl.to(
          ".imgrow1",
          {
            marginBottom: `5vw`,
          },
          "<"
        );
        tl.to(
          ".imgrow2",
          {
            marginBottom: `20vw`,
          },
          "<"
        );

        tl.to(
          ".imgrow3",
          {
            marginBottom: `40vw`,
          },
          "<"
        );
        tl.to(
          ".imgrow4",
          {
            marginBottom: `60vw`,
          },
          "<"
        );
        tl.to(
          ".herotext",
          {
            opacity: 1,
            delay: 0.2,
          },
          "<"
        );
        tl.to(
          ".content",
          {
            backdropFilter: "blur(30px)",
          },
          "<"
        );
        tl.to(".scrolling",{
          width: "100%",
      },"<")
      }
      page1();

      function page3() {
        gsap.to(".page3", {
          backgroundColor: `white`,
          color: `black`,
          duration: 1,
          scrollTrigger: {
            trigger: `.page2`,
            start: "90% 10%",
            end: "100% 50%",
            scrub: 3,
          },
        });
      }
      page3();

      function page4() {
        let tl4 = gsap.timeline({
          scrollTrigger:{
              trigger: ".part-4",
              start:"50% 50%",
              end: "200% 50%",
              pin: true,
              // markers: true,
              scrub: 1,
          },  
      });
      tl4.to(".c-one",{
          marginTop: "-25%",
          opacity:"1",
      }, 'sct-1')
      tl4.to(".c-two",{
          opacity:"1",
      }, 'sct-2')
      tl4.to(".c-one",{
          marginTop: "-100",
          opacity:"0",
      }, 'sct-2')
      tl4.to(".c-three",{
          opacity:"1",
      }, 'sct-3')
      tl4.to(".c-two",{
          opacity:"0",
      }, 'sct-3')
      tl4.to(".c-one",{
          marginTop:"-180%",
      }, 'sct-3')
      tl4.to(".c-one",{
          marginTop:"-230%",
      }, 'sct-4')
      tl4.to(".c-three",{
          opacity:"0",
      }, 'sct-4')
      tl4.to(".cir-part-4",{
          marginLeft:"70vw",
          rotate: 360,
          duration:1
      }, 'sct-4')
      }
      page4();
    }
  }
  GSAP();
});

// function middleware() {
//   function authMiddleware(next) {
//     const user =
//       localStorage.getItem("isLoggedIn") === "true"
//         ? { account_number: localStorage.getItem("account_number") }
//         : null;

//     if (user) {
//       console.log("User is authenticated:", user);
//       next(user);
//       setTimeout(() => {
//         localStorage.clear(); // Clear localStorage after authentication check
//       }, 9000);
//     } else {
//       // alert("Unauthorized!");
//       // localStorage.clear();
//       // window.location.href = "Pages/login.html";
//       Toastify({
//         text: "Unauthorized! Redirecting to login...",
//         duration: 3000,
//         close: true,
//         gravity: "top", // top or bottom
//         position: "right", // left, center or right
//         backgroundColor: "#ff5f5f",
//         stopOnFocus: true,
//       }).showToast();

//       setTimeout(() => {
//         localStorage.clear();
//         window.location.href = "Pages/login.html";
//       }, 2000);
//     }
//   }
// }
//   function mainApp(user) {
//     console.log("User is allowed, app running with:", user);
//     // use a script tag or an external JS file
//
//   }

//   function middleware() {
//     authMiddleware(mainApp);
//   }

//   middleware();

// middleware();
