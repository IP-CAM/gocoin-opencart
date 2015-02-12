Version 1.0.2

2014 GoCoin Holdings Limited and GoCoin International Group of companies hereby grants you permission to utilize a copy of this software and documentation in connection with your use of the GoCoin.com service subject the the published Terms of Use and Privacy Policy published on the site and subject to change from time to time at the discretion of GoCoin.<br><br>

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE DEVELOPERS OR AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.<br><br>

## Using the Official GoCoin OpenCart Plugin
When a shopper chooses the GoCoin payment method and places their order, they will be redirected to gateway.GoCoin.com to pay.  
GoCoin will send a notification to your server which this plugin handles.  Then the customer will be redirected to an order summary page.  

The order status in the admin panel will be "Processed" when the order is placed and payment has been confirmed. 

#### Important Note: 
This plugin supports Litecoin, Dogecoin, Bitcoin

### 1. Installation
[Opencart](http://www.opencart.com/) must be installed before installing this plugin.

a. 	Copy the contents in admin folder to corresponding folders in admin folder where Opencart is installed.<br>
b. 	Copy the contents in catalog folder to corresponding folders in catalog folder where Opencart is installed.<br>
c. 	Copy the contents in system folder to corresponding folder in system where Opencart is installed. This is the core library to make GoCoin API calls.<br><br>

### 2. Get API Key.
1) [Enable the GoCoin Hosted Payment Gateway](http://www.gocoin.com/docs/hosted_gateway)<br>
2) Get API Key from [GoCoin Dashboard](https://dashboard.gocoin.com)<br>
	The GoCoin Dashboard gives you the ability to obtain an API Key<br>
	Click On Developers<br>

##### Navigate to the Developers menu from the dashboard home<br>
![Developers](https://dl.dropboxusercontent.com/s/s4aevk5gig3x0g6/screenshot.png)


More information can be found [here](http://www.gocoin.com/pdfs/merchant_integration_guide_1.0.0.pdf)

### 3. Configuration

1. In the Admin panel click Extensions > Payments , You will see list of Payment module. Locate GoCoin, it have initially be disabled with an "install" link. Click Install. <br><br>

2. Configure GoCoin Payment extension, click on Edit link for GoCoin in Admin<br>
  a) Enter Merchant ID 
  b) Enter API Key
3. SAVE AGAIN. You are now ready to accept payments with GoCoin!
