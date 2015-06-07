<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\EventDispatcher;
use Piwik\Piwik;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\TrackerCodeGenerator;

/**
 * @group Core
 */
class TrackerCodeGeneratorTest extends IntegrationTestCase
{
    public function testJavascriptTrackingCode_withAllOptions()
    {
        $generator = new TrackerCodeGenerator();

        $jsTag = $generator->generate($idSite = 1, $piwikUrl = 'http://localhost/piwik',
            $mergeSubdomains = true, $groupPageTitlesByDomain = true, $mergeAliasUrls = true,
            $visitorCustomVariables = array(array("name", "value"), array("name 2", "value 2")),
            $pageCustomVariables = array(array("page cvar", "page cvar value")),
            $customCampaignNameQueryParam = "campaignKey", $customCampaignKeywordParam = "keywordKey",
            $doNotTrack = true);

        $expected = "&lt;!-- Piwik --&gt;
&lt;script type=&quot;text/javascript&quot;&gt;
  var _paq = _paq || [];
  _paq.push([\"setDocumentTitle\", document.domain + \"/\" + document.title]);
  // you can set up to 5 custom variables for each visitor
  _paq.push([\"setCustomVariable\", 1, \"name\", \"value\", \"visit\"]);
  _paq.push([\"setCustomVariable\", 2, \"name 2\", \"value 2\", \"visit\"]);
  // you can set up to 5 custom variables for each action (page view, download, click, site search)
  _paq.push([\"setCustomVariable\", 1, \"page cvar\", \"page cvar value\", \"page\"]);
  _paq.push([\"setCampaignNameKey\", \"campaignKey\"]);
  _paq.push([\"setCampaignKeywordKey\", \"keywordKey\"]);
  _paq.push([\"setDoNotTrack\", true]);
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=&quot;//localhost/piwik/&quot;;
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 1]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
&lt;/script&gt;
&lt;noscript&gt;&lt;p&gt;&lt;img src=&quot;//localhost/piwik/piwik.php?idsite=1&quot; style=&quot;border:0;&quot; alt=&quot;&quot; /&gt;&lt;/p&gt;&lt;/noscript&gt;
&lt;!-- End Piwik Code --&gt;
";

        $this->assertEquals($expected, $jsTag);
    }

    /**
     * Tests the generated JS code with protocol override
     */
    public function testJavascriptTrackingCode_withAllOptionsAndProtocolOverwrite()
    {
        /** @var EventDispatcher $eventObserver */
        $eventObserver = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\EventDispatcher');

        $generator = new TrackerCodeGenerator();

        $eventObserver->addObserver('Piwik.getJavascriptCode', function (&$codeImpl) {
            $codeImpl['protocol'] = 'https://';
        });

        $jsTag = $generator->generate($idSite = 1, $piwikUrl = 'http://localhost/piwik',
            $mergeSubdomains = true, $groupPageTitlesByDomain = true, $mergeAliasUrls = true,
            $visitorCustomVariables = array(array("name", "value"), array("name 2", "value 2")),
            $pageCustomVariables = array(array("page cvar", "page cvar value")),
            $customCampaignNameQueryParam = "campaignKey", $customCampaignKeywordParam = "keywordKey",
            $doNotTrack = true);

        $expected = "&lt;!-- Piwik --&gt;
&lt;script type=&quot;text/javascript&quot;&gt;
  var _paq = _paq || [];
  _paq.push([\"setDocumentTitle\", document.domain + \"/\" + document.title]);
  // you can set up to 5 custom variables for each visitor
  _paq.push([\"setCustomVariable\", 1, \"name\", \"value\", \"visit\"]);
  _paq.push([\"setCustomVariable\", 2, \"name 2\", \"value 2\", \"visit\"]);
  // you can set up to 5 custom variables for each action (page view, download, click, site search)
  _paq.push([\"setCustomVariable\", 1, \"page cvar\", \"page cvar value\", \"page\"]);
  _paq.push([\"setCampaignNameKey\", \"campaignKey\"]);
  _paq.push([\"setCampaignKeywordKey\", \"keywordKey\"]);
  _paq.push([\"setDoNotTrack\", true]);
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=&quot;https://localhost/piwik/&quot;;
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 1]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
&lt;/script&gt;
&lt;noscript&gt;&lt;p&gt;&lt;img src=&quot;https://localhost/piwik/piwik.php?idsite=1&quot; style=&quot;border:0;&quot; alt=&quot;&quot; /&gt;&lt;/p&gt;&lt;/noscript&gt;
&lt;!-- End Piwik Code --&gt;
";

        $this->assertEquals($expected, $jsTag);
    }

    /**
     * Tests the generated JS code with options before tracker url
     */
    public function testJavascriptTrackingCode_withAllOptionsAndOptionsBeforeTrackerUrl()
    {
        /** @var EventDispatcher $eventObserver */
        $eventObserver = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\EventDispatcher');

        $generator = new TrackerCodeGenerator();

        $eventObserver->addObserver('Piwik.getJavascriptCode', function (&$codeImpl) {
            $codeImpl['optionsBeforeTrackerUrl'] .= "_paq.push(['setAPIUrl', 'http://localhost/statistics']);\n    ";
        });

        $jsTag = $generator->generate($idSite = 1, $piwikUrl = 'http://localhost/piwik',
            $mergeSubdomains = true, $groupPageTitlesByDomain = true, $mergeAliasUrls = true,
            $visitorCustomVariables = array(array("name", "value"), array("name 2", "value 2")),
            $pageCustomVariables = array(array("page cvar", "page cvar value")),
            $customCampaignNameQueryParam = "campaignKey", $customCampaignKeywordParam = "keywordKey",
            $doNotTrack = true);

        $expected = "&lt;!-- Piwik --&gt;
&lt;script type=&quot;text/javascript&quot;&gt;
  var _paq = _paq || [];
  _paq.push([\"setDocumentTitle\", document.domain + \"/\" + document.title]);
  // you can set up to 5 custom variables for each visitor
  _paq.push([\"setCustomVariable\", 1, \"name\", \"value\", \"visit\"]);
  _paq.push([\"setCustomVariable\", 2, \"name 2\", \"value 2\", \"visit\"]);
  // you can set up to 5 custom variables for each action (page view, download, click, site search)
  _paq.push([\"setCustomVariable\", 1, \"page cvar\", \"page cvar value\", \"page\"]);
  _paq.push([\"setCampaignNameKey\", \"campaignKey\"]);
  _paq.push([\"setCampaignKeywordKey\", \"keywordKey\"]);
  _paq.push([\"setDoNotTrack\", true]);
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=&quot;//localhost/piwik/&quot;;
    _paq.push(['setAPIUrl', 'http://localhost/statistics']);
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 1]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
&lt;/script&gt;
&lt;noscript&gt;&lt;p&gt;&lt;img src=&quot;//localhost/piwik/piwik.php?idsite=1&quot; style=&quot;border:0;&quot; alt=&quot;&quot; /&gt;&lt;/p&gt;&lt;/noscript&gt;
&lt;!-- End Piwik Code --&gt;
";

        $this->assertEquals($expected, $jsTag);
    }
}
