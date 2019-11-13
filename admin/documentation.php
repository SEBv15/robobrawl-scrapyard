<div class="content">
<h1>Documentation</h1>
<h2>How it works</h2>
<p>This plugin simplifies the creation and maintenance of the scrapyards by storing all the data in a database and automatically 
generating pages and links that can be placed anywhere on the website.</p>
<p>Each bot can be modified over the admin panel and then linked to by copying the <code>[sy_card id="?"]</code> shortcode.</p>
<h2>Installation</h2>
<p>After installing the plugin, you are most likely going to run into the issue that the bot page (<code>/scrapyard-bot/?bot_id=...</code>) will say something like "Page Not Found". 
This is because wordpress does not have the plugins rule loaded yet. To fix this simply go to settings->permalinks and hit "Save Changes".
<h2>Available Shortcodes</h2>
<h3><code>[sy_card]</code></h3>
<p>This is a shortcode that inserts a link to the bots page in form of an image with the bots titled overlayed. 
It requires the <code>id="?"</code> attribute. The <code>title="?"</code> attribute is purely cosmetic and is disregarded by the plugin.</p>
<h3><code>[sy_cards] ... [/sy_cards]</code></h3>
<p><code>[sy_card]</code> shortcodes should be wrapped by this shortcode to allow for proper alignment.</p>
<h3><code>[sy_search]</code></h3>
<p>This shortcode inserts a searchbar in the page which filters any <code>[sy_card]</code> on the same page for the search input and hides all that don't match.</p>
<h2>Example</h2>
<pre><code>[sy_search]
&lt;h1&gt;Look at my awesome bots!&lt;/h1&gt;
[sy_cards]
[sy_card id="0"]
[sy_card id="1"]
[/sy_cards]</code></pre>
<h2>Bot Data</h2>
<h3>Name</h3>
<p>Your bot's name.</p>
<h3>Photos</h3>
<p>Photos from the wordpress media gallery that will be shown as a slideshow. The first image is the photo on the sy_card.</p>
<h3>Description</h3>
<p>A short description of the bot. HTML works but should preferably be restricted to a minimum.</p>
<h3>Attributes</h3>
<p>Any stats that you want to include with your bot like wins/losses. <b>Please set a type attribute so types can be searched more easily.</b></p>
<h3>Extra HTML</h3>
<p>Probably won't be used much, but it provides an option to add some HTML at the bottom of the bot page in case needed.</p>
<h2>Editing the bot page layout</h2>
<b><i>Only do this if really needed and you know what you're doing</i></b>
<p>Go to plugins->edit plugin and select scrapyard. Then open the bot-page folder. The files named bot-page.* contain all the HTML, CSS, and JavaScript for the bot-page.</p>
<p>In case you are reading this because someone switched the theme and the title is not working anymore, the_archive_title contains the page title for the audioman theme. 
I don't know if it's the same for all themes and if switching the theme broke it, good luck! It took me at least two hours to figure this out.</p>
<br><br>
<i>I hate wordpress</i>
<br><br>
<b>NO!</b>
<br>|
<br>V
</div>
<style>
	div.content {
		max-width: 90%;
		width: 50em;
	}
</style>