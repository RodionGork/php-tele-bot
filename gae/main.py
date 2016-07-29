import webapp2
import urllib2
import json

apiurl = 'https://api.telegram.org/bot'
with open('token.txt') as f:
    token = f.read().strip()

def makeCall(fun, data):
    clen = len(data)
    req = urllib2.Request(apiurl + token + '/' + fun, data, {'Content-Type': 'application/json', 'Content-Length': clen})
    response = urllib2.urlopen(req)
    text = response.read()
    response.close()
    return text

def processMessage(chat, text):
    if text == '/start':
        text = 'This is a test bot'
    elif text == '/help':
        text = 'Type any text, it will be reverted'
    else:
        text = text[::-1]
    makeCall('sendMessage', json.dumps({'chat_id':chat,'text':text}))

class MainPage(webapp2.RequestHandler):
    def get(self):
        self.response.headers['Content-Type'] = 'text/html'
        self.response.write('<h1>I\'m a human v1.001</h1>')
    def post(self):
        data = json.loads(self.request.body)
        msg = data['message']
        processMessage(msg['chat']['id'], msg['text'])
        self.response.headers['Content-Type'] = 'text/plain'
        self.response.write('')
    
app = webapp2.WSGIApplication([
    ('/', MainPage),
], debug=True)

