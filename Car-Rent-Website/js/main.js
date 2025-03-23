(function ($) {
    "use strict";

    /*==================================================================
    [ Focus Contact2 ]*/
    $('.input100').each(function(){
        $(this).on('blur', function(){
            if($(this).val().trim() != "") {
                $(this).addClass('has-val');
            }
            else {
                $(this).removeClass('has-val');
            }
        })    
    })


    /*==================================================================
    [ Validate after type ]*/
    $('.validate-input .input100').each(function(){
        $(this).on('blur', function(){
            if(validate(this) == false){
                showValidate(this);
            }
            else {
                $(this).parent().addClass('true-validate');
            }
        })    
    })

    /*==================================================================
    [ Validate ]*/
    var input = $('.validate-input .input100');

    $('.validate-form').on('submit',function(){
        var check = true;

        for(var i=0; i<input.length; i++) {
            if(validate(input[i]) == false){
                showValidate(input[i]);
                check=false;
            }
        }

        return check;
    });


    $('.validate-form .input100').each(function(){
        $(this).focus(function(){
           hideValidate(this);
           $(this).parent().removeClass('true-validate');
        });
    });

    function validate (input) {
        if($(input).attr('type') == 'email' || $(input).attr('name') == 'email') {
            if($(input).val().trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/) == null) {
                return false;
            }
        }
        else {
            if($(input).val().trim() == ''){
                return false;
            }
        }
    }

    function showValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).addClass('alert-validate');
    }

    function hideValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).removeClass('alert-validate');
    }
    
    // Mobile Menu Toggle - Improved implementation
    document.addEventListener('DOMContentLoaded', () => {
      const menuToggle = document.querySelector('.menu-toggle');
      const navMenu = document.querySelector('.nav-menu');
      const body = document.body;

      if (menuToggle && navMenu) {
        // Fix for the burger menu click event
        menuToggle.addEventListener('click', (e) => {
          e.stopPropagation(); // Prevent event from bubbling up
          menuToggle.classList.toggle('active');
          navMenu.classList.toggle('active');
          body.classList.toggle('menu-open');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
          if (menuToggle.classList.contains('active') && 
              !menuToggle.contains(e.target) && 
              !navMenu.contains(e.target)) {
            menuToggle.classList.remove('active');
            navMenu.classList.remove('active');
            body.classList.remove('menu-open');
          }
        });
      }

      // Navbar scroll effect
      const navbar = document.querySelector('.navbar');
      if (navbar) {
        window.addEventListener('scroll', () => {
          if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
          } else {
            navbar.classList.remove('scrolled');
          }
        });
      }
      
      // Add touch support for mobile devices
      if (menuToggle && 'ontouchstart' in window) {
        menuToggle.addEventListener('touchstart', (e) => {
          e.stopPropagation();
        });
      }
      
      // Profile Dropdown functionality
      initializeProfileDropdown();
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
          targetElement.scrollIntoView({
            behavior: 'smooth'
          });
          
          // Close mobile menu if open
          const menuToggle = document.querySelector('.menu-toggle');
          const navMenu = document.querySelector('.nav-menu');
          if (menuToggle && navMenu) {
            menuToggle.classList.remove('active');
            navMenu.classList.remove('active');
            document.body.classList.remove('menu-open');
          }
        }
      });
    });

    // Profile Dropdown functionality
    const initializeProfileDropdown = () => {
      const profileToggle = document.querySelector('.profile-toggle');
      const profileMenu = document.querySelector('.profile-menu');
      
      if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          profileMenu.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
          if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
            profileMenu.classList.remove('active');
          }
        });
        
        // Add touch support
        if ('ontouchstart' in window) {
          profileToggle.addEventListener('touchstart', (e) => {
            e.stopPropagation();
          });
        }
      }
    };

    // Profile Dropdown functionality with console logging
    document.addEventListener('DOMContentLoaded', function() {
      console.log('DOM loaded, initializing profile dropdown');
      const profileToggle = document.querySelector('.profile-toggle');
      const profileMenu = document.querySelector('.profile-menu');
      
      if (profileToggle && profileMenu) {
        console.log('Profile elements found');
        
        profileToggle.addEventListener('click', function(e) {
          console.log('Profile toggle clicked');
          e.preventDefault();
          e.stopPropagation();
          profileMenu.classList.toggle('active');
          console.log('Menu active:', profileMenu.classList.contains('active'));
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
          if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
            profileMenu.classList.remove('active');
            console.log('Clicking outside, closing menu');
          }
        });
      } else {
        console.log('Profile elements not found:', {
          toggle: !!profileToggle,
          menu: !!profileMenu
        });
      }
    });

})(jQuery);