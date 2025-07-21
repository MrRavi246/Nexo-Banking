function lenis() {
  // Initialize Lenis
  const lenis = new Lenis({
    autoRaf: true,
    smoothWheel: true,
    lerp: 0.5,
    smoothTouch: true,
    touchMultiplier: 2,
    duration: 0.5,
  });

  // Listen for the scroll event and log the event data
  lenis.on("scroll", (e) => {});
}
lenis();

// to remove right click
document.addEventListener(
  "contextmenu",
  function (e) {
    e.preventDefault();
  },
  false
);

// use a script tag or an external JS file
document.addEventListener("DOMContentLoaded", (event) => {
  // gsap code here!

  function GSAP() {
    if (window.gsap && window.ScrollTrigger) {
      gsap.registerPlugin(ScrollTrigger, SplitText);

      function loader() {
        // to remove scrollbar
        document.body.style.overflow = "hidden";

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
    }
  }
  GSAP();
});
