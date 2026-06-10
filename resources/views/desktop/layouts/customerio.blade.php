<script>
    !function(){var i="cioanalytics", analytics=(window[i]=window[i]||[]);if(!analytics.initialize)if(analytics.invoked)window.console&&console.error&&console.error("Snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","debug","page","once","off","on","addSourceMiddleware","addIntegrationMiddleware","setAnonymousId","addDestinationMiddleware"];analytics.factory=function(e){return function(){var t=Array.prototype.slice.call(arguments);t.unshift(e);analytics.push(t);return analytics}};for(var e=0;e<analytics.methods.length;e++){var key=analytics.methods[e];analytics[key]=analytics.factory(key)}analytics.load=function(key,e){var t=document.createElement("script");t.type="text/javascript";t.async=!0;t.setAttribute('data-global-customerio-analytics-key', i);t.src="https://cdp-eu.customer.io/v1/analytics-js/snippet/" + key + "/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(t,n);analytics._writeKey=key;analytics._loadOptions=e};analytics.SNIPPET_VERSION="4.15.3";
        analytics.load(
            "981a2ee5c5d668adbde4",
            {
                "integrations": {
                    "Customer.io In-App Plugin": {
                        siteId: "170a79de47727b679b53",
                        anonymousInApp: true,
                        events: function(event) {
                            console.log(event);
                        }
                    }
                }
            }
        );
        analytics.page();
    }}();
</script>

@if(\Illuminate\Support\Facades\Auth::check())
    <script async>
        setTimeout(() => {
            cioanalytics.identify({{\Illuminate\Support\Facades\Auth::id()}}, function(){
                // console.log("identified");
            });
        }, 1000);
    </script>
@endif
