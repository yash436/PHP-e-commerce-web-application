# PHP-e-commerce-web-application
A trivial web application that allows customers to buy products

- Used PHP sessions, the PHP SimpleXML interface and the shopping.com API i.e. eBay Commerce Network API ("ECN API"). Using a demo API key but you can get your own API key as well.
- The search form has a menu to select a category, a text window to specify search keywords, and a submit button. The menu contains all sub-categories of the category "Computers". The menu items are not handwritten in the code; instead, they are generated in the PHP code.
- The result of a keyword search contains up to 20 products within the selected category that best match the keyword query.
- PHP session is used to store the shopping basket. For each chosen item, we store the Id, the name, the price, the image, and the link to the shopping.com web page that lists the best offers for this item.
