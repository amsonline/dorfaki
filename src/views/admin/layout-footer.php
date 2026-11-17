      </main>
  </div>

  <script>
    // Highlight the active sidebar link based on current path
    $(document).ready(function () {
      const currentPath = window.location.pathname;
      $('.admin-nav a').each(function () {
        const $link = $(this);
        const href = $link.attr('href');
        if (href && currentPath === href) {
          $link.addClass('active');
        } else {
          $link.removeClass('active');
        }
      });
    });
  </script>
</body>

</html>
