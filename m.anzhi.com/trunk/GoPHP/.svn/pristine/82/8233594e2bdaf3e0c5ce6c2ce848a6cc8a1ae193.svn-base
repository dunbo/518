#!/usr/bin/env ruby

require 'rubygems'
require 'yaml'
require 'xmpp4r'
require 'xmpp4r/roster'
require 'json'
require 'socket'
require 'logger'
require 'date'

# this script starts a TCP server listens on a local port
# and retreive a JSON string contains two fields: "to" and "body"
# then deliver this over a configured XMPP server

# parse configuration file
$config = File.dirname(__FILE__) + '/config.yml'
$config = YAML.load_file($config)
temp = $config['config']
$config = Hash.new
temp.each { |a|
  a.each { |k, v| $config[k] = v }
}
# try to retrieve iface addr
sock = Socket.new(Socket::AF_INET, Socket::SOCK_DGRAM,0)
buff = ['eth0', ""].pack('a16h16')
#sock.ioctl(SIOCGIFADDR, buff);
sock.ioctl(0x8915, buff);
sock.close
ip = Socket.unpack_sockaddr_in(buff[16..48])[1]
$config['xmpp_resource'] = ip

Thread.abort_on_exception = true

File.open($config['pid'], 'w') { |f| f.write($$) }

class JServer
  def initialize(port, jid = 'jabber@example.com', jpwd = 'jabber', log_path = '/tmp/xmppd.log')
    @lock = Mutex.new
    @cond = ConditionVariable.new
    @request = Array.new
    @jid = jid
    @jpwd = jpwd
    @server = TCPServer.new('127.0.0.1', port)
    @signal = false
    @messenger = nil
    @roster = nil
    @logger = Logger.new(log_path)
    trap('INT') {
      stop
    }
  end

  def start
    @signal = true
    retried = 0
    while @messenger == nil || @roster == nil
      begin
        @messenger = Jabber::Client.new(@jid)
        @messenger.connect
        @messenger.auth(@jpwd)
        @messenger.send(Jabber::Presence.new.set_type:chat)
        @logger.debug(@jid + ' is online')
        @roster = Jabber::Roster::Helper.new(@messenger)
        @roster.add_subscription_request_callback { |i,p|
          @roster.accept_subscription(p.from)
          x = Jabber::Presence.new
          x.set_to(p.from)
          x.set_type(:subscribe)
          @messenger.send(x)
        }
        @roster.get_roster
        @roster.wait_for_roster
        @logger.debug(@jid + ' roster is ready')
      rescue
        @logger.debug($@.to_s)
        retried = retried + 1
        throw $@ if retried > 99
        sleep 5
      end
    end
    @s_thread = Thread.new {
      while (@signal)
        begin
          c = @server.accept_nonblock
        rescue IO::WaitReadable, Errno::EINTR
          if !@signal
            break
          else
            sleep(0.01)
            next
          end
        end
        r = c.gets
        c.close
        @lock.synchronize {
          @request.push(r)
          @cond.broadcast
        }
      end
      @server.close
      @logger.debug('s_thread exited')
    }
    @j_thread = Thread.new {
      while (@signal)
        message = nil
        abort = false
        @lock.synchronize {
          while @request.size == 0
            @cond.wait(@lock)
            if @request.size == 0
              abort = true
            else
              message = @request.pop
            end
            break
          end
        }
        break if abort
        begin
          message = JSON(message)
        rescue
          next
        end
        @logger.debug(message)
        message_to = message['to']
        message_body = message['body']
        begin
          if @roster.find(message_to).size == 0
            x = Jabber::Presence.new
            x.set_to(message_to)
            x.set_type(:subscribe)
            @messenger.send(x)
          end
          ip = $config['xmpp_resource']
          ts = DateTime.now.to_s
          message_body ="IP: #{ip}\nDate: #{ts}\nMessage: #{message_body}\n"
          message = Jabber::Message.new(message_to, message_body)
          message.type = :chat
          @messenger.send(message)
        rescue
          @logger.debug($@.to_s)
        end
      end
      @logger.debug('j_thread exited')
    }
  end

  def loop
    @s_thread.join
    @j_thread.join
  end

  def stop
    @signal = false
    @lock.synchronize {
    	@logger.debug('notify all threads to exit')
    	@cond.broadcast
    }
  end

end

Process.daemon(true)

js = JServer.new($config['port'], $config['xmpp_username'] + '@' + $config['xmpp_domain'] + '/' + $config['xmpp_resource'], $config['xmpp_password'], $config['log'])
js.start
js.loop

File.delete($config['pid'])

