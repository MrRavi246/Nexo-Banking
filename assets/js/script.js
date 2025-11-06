function lenis() {
  // Initialize Lenis
  const lenis = new Lenis({
    autoRaf: true,
    smoothWheel: true,
    lerp: 0.1,
    smoothTouch: true,
    touchMultiplier: 2,
    duration: 1.5,
  });

  // Sync ScrollTrigger with Lenis
  lenis.on("scroll", ScrollTrigger.update);

  // Listen for the scroll event and log the event data
  lenis.on("scroll", (e) => {});

  function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
  }
  requestAnimationFrame(raf);
}
// lenis();

// Force ScrollTrigger to refresh after Lenis is ready
window.addEventListener("load", () => {
  setTimeout(() => {
    ScrollTrigger.refresh();
  }, 100); // slight delay ensures DOM is ready
});

// to remove right click
// document.addEventListener(
//   "contextmenu",
//   function (e) {
//     e.preventDefault();
//   },
//   false
// );

// to remove scrollbar
// document.body.style.overflow = "hidden";



document.addEventListener("DOMContentLoaded", (event) => {
  // gsap code here!
  function GSAP() {
    if (window.gsap && window.ScrollTrigger) {
      gsap.registerPlugin(ScrollTrigger, SplitText);

      function loader() {
        let split = SplitText.create(".con", { type: "chars" });

          let tl = gsap.timeline();

          // hide main while loader animation runs
          tl.set("main", { display: "none" });

          tl.from(split.chars, {
            duration: 0.8,
            x: gsap.utils.random(-100, 100),
            y: gsap.utils.random(-100, 100),
            autoAlpha: 0,
            stagger: 0.05,
            ease: "back.out(0.7)",
          });

          tl.to(split.chars, {
            duration: 0.5,
            x: gsap.utils.random(-50, 50),
            y: gsap.utils.random(-100, 100),
            autoAlpha: 0,
            stagger: 0.05,
            ease: "back.in(2)",
          });

          tl.to(".loader", {
            duration: 0.3,
            autoAlpha: 0,
            display: "none",
            backgroundColor: `rgba(0, 0, 0, 0.77)`,
          });

          tl.to("main", {
            display: "block",
            delay: -0.7,
          });
        // try {
        //   if (!window.SplitText) throw new Error('SplitText plugin not found');

          
        // } catch (err) {
        //   // If SplitText or GSAP animation fails, ensure loader is removed so page is scrollable
        //   console.warn('Loader animation skipped:', err);
        //   const loaderEl = document.querySelector('.loader');
        //   const mainEl = document.querySelector('main');
        //   if (loaderEl) {
        //     loaderEl.style.display = 'none';
        //     loaderEl.style.opacity = '0';
        //     loaderEl.style.pointerEvents = 'none';
        //   }
        //   if (mainEl) {
        //     mainEl.style.display = 'block';
        //   }
        // }
      }
      loader();

      function page1() {
        const tl = gsap.timeline({
          scrollTrigger: {
            trigger: ".page1",
            start: "50% 50%",
            end: "200% 0%",
            scrub: true,
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
            // delay: 0.2,
            backgroundColor: `rgba(0, 0, 0, 0.77)`,
          },
          "<"
        );
        tl.to(
          ".content",
          {
            backdropFilter: "blur(10px)",            
          },
          "<"
        );
        tl.to(
          ".scrolling",
          {
            width: "100%",
          },
          "<"
        );
      }
      page1();

      function page3() {
        gsap.to(".page3", {
          backgroundColor: `white`,
          color: `black`,
          borderColor: "black",
          duration: 1,
          scrollTrigger: {
            trigger: `.page2`,
            start: "80% 10%",
            end: "100% 50%",
            scrub: 2,
          },
        },"<");
        gsap.to(".page3 .rowRound", {          
          borderColor: "black",
          duration: 1,
          scrollTrigger: {
            trigger: `.page2`,
            start: "80% 10%",
            end: "100% 50%",
            scrub: 2,
          },
        },"<");
        gsap.to(".page3 .number", {          
          borderColor: "black",
          duration: 1,
          scrollTrigger: {
            trigger: `.page2`,
            start: "80% 10%",
            end: "100% 50%",
            scrub: 2,
          },
        },"<");
      }
      page3();

      function page4() {
        let tl4 = gsap.timeline({
          scrollTrigger: {
            trigger: ".part-4",
            start: "50% 50%",
            end: "200% 50%",
            pin: true,
            // markers: true,
            scrub: 1,
          },
        });
        tl4.to(
          ".c-one",
          {
            marginTop: "-25%",
            opacity: "1",
          },
          "sct-1"
        );
        tl4.to(
          ".c-two",
          {
            opacity: "1",
          },
          "sct-2"
        );
        tl4.to(
          ".c-one",
          {
            marginTop: "-100",
            opacity: "0",
          },
          "sct-2"
        );
        tl4.to(
          ".c-three",
          {
            opacity: "1",
          },
          "sct-3"
        );
        tl4.to(
          ".c-two",
          {
            opacity: "0",
          },
          "sct-3"
        );
        tl4.to(
          ".c-one",
          {
            marginTop: "-180%",
          },
          "sct-3"
        );
        tl4.to(
          ".c-one",
          {
            marginTop: "-230%",
          },
          "sct-4"
        );
        tl4.to(
          ".c-three",
          {
            opacity: "0",
          },
          "sct-4"
        );
        tl4.to(
          ".cir-part-4",
          {
            marginLeft: "70vw",
            rotate: 360,
            duration: 3,
            backgroundColor: "#20ba58",
          },
          "sct-4"
        );
      }
      page4();

      function page5() {
        let tl7 = gsap.timeline({
          scrollTrigger: {
            trigger: ".part-7",
            start: "50% 50%",
            end: "200% -50%",
            pin: `.page5`,
            scrub: 1,
          },
        });
        tl7.to("#demo", {
          bottom: "7%",
        });
        tl7.to(
          ".our-work-txt-div",
          {
            height: "60vh",
            duration: 5,
          },
          "height"
        );
        tl7.to(
          ".our-work-txt",
          {
            height: "60vh",
            duration: 5,
          },
          "height"
        );
        tl7.to(
          "#our",
          {
            left: "0%",
            color: "#eb7ef2",
            duration: 5,
          },
          "height"
        );
        tl7.to(
          "#work",
          {
            right: "0%",
            color: "white",
            duration: 5,
          },
          "height"
        );
        tl7.to(".scroll-img", {
          marginTop: "-300%",
          duration: 10,
        });
      }
      page5();
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
