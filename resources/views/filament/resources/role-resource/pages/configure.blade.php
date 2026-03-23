@push('scripts')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jstree/dist/jstree.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('js/jstree/dist/themes/default/style.min.css') }}" />
@endpush

<x-filament::page>
    <div class="p-4 w-full bg-white rounded-lg border shadow-md sm:p-8 dark:bg-gray-800 dark:border-gray-700">
        <div id="jstree-div" wire:ignore></div>
    </div>
</x-filament::page>

<script>
    window.addEventListener('DOMContentLoaded', function() {
        jstreeInit();
    });

    function jstreeInit() {
        jsTreePerm = $('#jstree-div');
        jsTreePerm.jstree({
                "core": {
                    "data": {{ Js::from($perms) }},
                    "check_callback": true,
                },
                "plugins": ["checkbox", "types"],
                "checkbox": {
                    "keep_selected_style": false
                },
                "types": {
                    "root": {
                        "icon": "fa fa-desktop text-primary"
                    },
                    "default": {
                        "icon": "fa fa-folder"
                    },
                    "key": {
                        "icon": "fa fa-key"
                    }
                }
            }).on('loaded.jstree', function(e, data) {
                let selected = @this.selected;
                let tree = data.instance;
                let nodes = tree.get_json('#', {
                    flat: true
                });
                tree.open_all();
                for (let node of nodes) {
                    if (selected.includes(node.id)) {
                        console.log(node.id);
                        tree.select_node(node.id);
                    }
                }
            })
            .on('changed.jstree', function(e, data) {
                var i, j, r = [];
                for (i = 0, j = data.selected.length; i < j; i++) {
                    r.push(data.instance.get_node(data.selected[i]).id);
                }
                @this.selected = r;
            });
    }
</script>
