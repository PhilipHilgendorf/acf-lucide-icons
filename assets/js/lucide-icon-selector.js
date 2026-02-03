(function($) {
    $(document).ready(function () {

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        $('.acf-lucideicons-list').each(function() {
            initIconSelector($(this));
        });

        function initIconSelector($list) {
            var $wrap = $list.closest('.acf-lucideicons-search-wrap');
            var $searchInput = $wrap.find('.acf-lucideicons-search-input');
            var batchSize = parseInt($list.data('batch-size')) || 100;

            var $jsonScript = $list.find('.acf-lucideicons-data');
            var allIcons = [];
            if ($jsonScript.length) {
                try {
                    allIcons = JSON.parse($jsonScript.text());
                } catch(e) {
                    console.error('Failed to parse icon data:', e);
                }
            }

            var renderedIcons = {};
            $list.find('.acf-lucideicons-container').each(function() {
                var iconName = $(this).attr('data-icon');
                if (iconName) {
                    renderedIcons[iconName] = true;
                }
            });

            var currentIndex = Object.keys(renderedIcons).length;
            var isSearching = false;
            
            function createIconHtml(icon) {
                return '<div class="acf-lucideicons-container" data-search="' + escapeAttr(icon.search) + '" data-icon="' + escapeAttr(icon.name) + '" title="' + escapeAttr(icon.label) + '">' +
                       '<div class="acf-lucideicons-inner"><i data-lucide="' + escapeAttr(icon.name) + '"></i></div></div>';
            }

            function escapeAttr(str) {
                return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            function loadMoreIcons(count) {
                if (isSearching) return;

                var added = 0;
                var html = '';

                for (var i = currentIndex; i < allIcons.length && added < count; i++) {
                    var icon = allIcons[i];
                    if (!renderedIcons[icon.name]) {
                        html += createIconHtml(icon);
                        renderedIcons[icon.name] = true;
                        added++;
                    }
                }

                if (html) {
                    $jsonScript.before(html);
                    currentIndex += added;

                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }

                    bindClickHandlers();
                }

                return added;
            }

            $list.on('scroll', function() {
                if (isSearching) return;

                var scrollTop = $list.scrollTop();
                var scrollHeight = $list[0].scrollHeight;
                var clientHeight = $list[0].clientHeight;

                if (scrollTop + clientHeight >= scrollHeight - 100) {
                    loadMoreIcons(batchSize);
                }
            });

            $searchInput.on('input', function() {
                var search = $(this).val().toLowerCase().trim();
                $wrap.find('.acf-lucideicons-list-empty').hide();

                if (search === '') {
                    isSearching = false;
                    $list.find('.acf-lucideicons-container').show();
                    return;
                }

                isSearching = true;

                var matchingIcons = allIcons.filter(function(icon) {
                    return icon.search.indexOf(search) !== -1;
                });

                if (matchingIcons.length === 0) {
                    $list.find('.acf-lucideicons-container').hide();
                    $wrap.find('.acf-lucideicons-list-empty .acf-invalid-lucideicons-search-term').text(search);
                    $wrap.find('.acf-lucideicons-list-empty').show();
                    return;
                }

                $list.find('.acf-lucideicons-container').hide();

                var shown = 0;
                var maxResults = 100;

                matchingIcons.forEach(function(icon) {
                    if (shown >= maxResults) return;

                    var $existing = $list.find('.acf-lucideicons-container[data-icon="' + icon.name + '"]');

                    if ($existing.length) {
                        $existing.show();
                    } else {
                        $jsonScript.before(createIconHtml(icon));
                        renderedIcons[icon.name] = true;
                    }
                    shown++;
                });

                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                bindClickHandlers();
            });

            function bindClickHandlers() {
                $list.find('.acf-lucideicons-container').off('click').on('click', function() {
                    $list.find('.acf-lucideicons-container').removeClass('selected');
                    $(this).addClass('selected');
                    var iconName = $(this).attr('data-icon');
                    var $input = $wrap.find('.acf-lucideicons-hidden-input');
                    $input.val(iconName).trigger('change');
                    console.log('Icon selected:', iconName, 'Input value:', $input.val());
                });
            }

            // Initial binden
            bindClickHandlers();
        }

    });
})(jQuery);
