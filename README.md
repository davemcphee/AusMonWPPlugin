## AusMonWPPlugin

Adds custom meta-box on post edit page. Metabox pulls data from minutes.city server, displays it for easy references. Clicking the insert button adds a custom a href link into the post right at the cursor, including an HTML comment containing the internal item ID.

On publishing the post, a wordpress hook parses the content of the post for HTML comments containing IDs, and POSTs that data to our listener, which updates our DB. 
