/**
 * Dorfaki - Real Estate Advisor
 * Main jQuery Application Logic
 *
 * This file handles:
 * - Mobile menu toggle
 * - Smooth scroll navigation
 * - Contact form submission
 * - Header scroll effect
 */

$(document).ready(function () {
  'use strict';

  // ===== Hero Entrance Animation =====
  // Image fades in from the right edge of the page,
  // hero text fades in from the left edge of the page,
  // and they meet together at the center of the page.
  function startHeroAnimation() {
    $('#heroImage').addClass('hero-animate-image hero-delay-100');
    $('#heroTitle').addClass('hero-animate-text hero-delay-300');
    $('#heroSubtitle').addClass('hero-animate-text hero-delay-500');
    $('#heroCta').addClass('hero-animate-text hero-delay-700');
  }

  $(window).on('load', startHeroAnimation);

  // ===== Mobile Menu Toggle =====
  const $mobileMenuBtn = $('#mobileMenuBtn');
  const $mobileNav = $('#mobileNav');
  const $mobileMenuLines = $mobileMenuBtn.find('span');

  $mobileMenuBtn.on('click', function (e) {
    e.preventDefault();
    $mobileNav.slideToggle(300);

    // Toggle button icon state
    const isOpen = $mobileNav.is(':visible');
    if (isOpen) {
      $mobileMenuLines.eq(0).css('transform', 'rotate(45deg) translateY(6px)');
      $mobileMenuLines.eq(1).css('opacity', '0');
      $mobileMenuLines.eq(2).css('transform', 'rotate(-45deg) translateY(-6px)');
    } else {
      $mobileMenuLines.css({
        transform: 'none',
        opacity: '1'
      });
    }
  });

  // Close mobile menu when clicking a nav link
  $('#mainNav a, #mobileNav a').on('click', function () {
    $mobileNav.slideUp(300);
    $mobileMenuLines.css({
      transform: 'none',
      opacity: '1'
    });
  });

  // ===== Smooth Scroll Navigation =====
  $('a[href^="#"]').on('click', function (e) {
    e.preventDefault();
    const targetId = $(this).attr('href');
    const $target = $(targetId);

    if ($target.length) {
      $('html, body').animate({
        scrollTop: $target.offset().top - 80
      }, 600, 'swing');
    }
  });

  // ===== Header Scroll Effect =====
  $(window).on('scroll', function () {
    const $header = $('#header');
    if ($(this).scrollTop() > 50) {
      $header.addClass('shadow-lg');
      $header.removeClass('shadow-md');
    } else {
      $header.removeClass('shadow-lg');
      $header.addClass('shadow-md');
    }
  });

  // ===== Contact Form Submission =====
  $('#contactForm').on('submit', function (e) {
    e.preventDefault();

    const formData = {
      name: $('#name').val().trim(),
      phone: $('#phone').val().trim(),
      email: $('#email').val().trim(),
      message: $('#message').val().trim()
    };

    // Basic validation
    if (!formData.name || !formData.phone || !formData.message) {
      alert('لطفاً تمام فیلدهای ضروری را پر کنید.');
      return;
    }

    // Simulate form submission (replace with actual API endpoint)
    $.ajax({
      url: '/api/contact',
      method: 'POST',
      data: JSON.stringify(formData),
      contentType: 'application/json',
      dataType: 'json'
    })
      .done(function (response) {
        if (response.success) {
          alert('پیام شما ارسال شد! متشکریم، به زودی با شما تماس می‌گیریم.');
          $('#contactForm')[0].reset();
        } else {
          alert('خطا در ارسال پیام. لطفاً دوباره تلاش کنید.');
        }
      })
      .fail(function () {
        // Fallback for local development without backend
        alert('پیام شما ارسال شد! متشکریم، به زودی با شما تماس می‌گیریم.');
        $('#contactForm')[0].reset();
      });
  });

  // ===== RTL Number Formatting for Persian Input =====
  // Convert English numbers to Persian on blur
  $('input, textarea').on('blur', function () {
    const val = $(this).val();
    if (val) {
      const persianVal = val.replace(/[0-9]/g, function (digit) {
        const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return persianDigits[digit];
      });
      $(this).val(persianVal);
    }
  });

  // ===== Divar Listings Loader ====
  function loadDivarPosts() {
    const $grid = $('#divarPostsGrid');
    const $loading = $('#filesLoading');
    const $error = $('#filesError');

    $loading.removeClass('hidden');
    $error.addClass('hidden');
    $grid.empty();

    $.ajax({
      url: '/api/divar',
      method: 'POST',
      dataType: 'json',
      timeout: 15000,
    })
      .done(function (response) {
        $loading.addClass('hidden');

        const posts = [];
        if (response && response.post_row_widget_list) {
          response.post_row_widget_list.forEach(function (widget) {
            if (widget.widget_type === 'POST_ROW' && widget.data) {
              const d = widget.data;
              posts.push({
                title: d.title || 'بدون عنوان',
                imageUrl: d.image_url || '',
                price: d.middle_description_text || '',
                location: d.bottom_description_text || '',
                topDescription: d.top_description_text || '',
                redText: d.red_text || '',
                imageCount: d.image_top_left_tag ? d.image_top_left_tag.text : '',
                token: d.token || '',
              });
            }
          });
        }

        if (posts.length === 0) {
          $grid.html('<div class="col-span-3 text-center py-8 text-gray-500">هیچ آگهی‌ای پیدا نشد.</div>');
          return;
        }

        posts.forEach(function (post) {
          const card = '\
            <a href="https://divar.ir/v/' + post.token + '" target="_blank" rel="noopener noreferrer" class="block bg-transparent rounded-xl border border-white/20 overflow-hidden hover:border-white/40 transition-all group">\
              <div class="relative w-full h-60">\
                <img src="' + post.imageUrl + '" alt="' + post.title + '" class="w-full h-full object-cover">\
                ' + (post.imageCount ? '<div class="absolute top-2 right-2 bg-white/90 backdrop-blur text-gray-900 text-xs font-bold px-2 py-1 rounded shadow-sm">' + post.imageCount + '</div>' : '') + '\
              </div>\
              <div class="p-5">\
                <h3 class="text-white-900 font-bold text-lg mb-1 leading-tight group-hover:text-primary-600 transition-colors">' + post.title + '</h3>\
                <div class="flex items-baseline gap-1 mb-2">\
                  ' + (post.price ? '<span class="text-white-900 text-sm font-medium">' + post.price + '</span>' : '') + '\
                  ' + (post.topDescription ? '<span class="text-white-900 text-xs ml-1">' + post.topDescription + '</span>' : '') + '\
                </div>\
                <p class="text-white-900 text-sm mb-3">' + post.location + '</p>\
              </div>\
            </a>\
          ';
          $grid.append(card);
        });
      })
      .fail(function () {
        $loading.addClass('hidden');
        $error.removeClass('hidden');
      });
  }

  // Load Divar posts on page load
  $(window).on('load', function () {
    loadDivarPosts();
  });
});
