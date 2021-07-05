const { createClient } = require('C:\\Users\\admin\\AppData\\Roaming\\npm\\node_modules\\@nexrender/api');
var needle = require('C:\\Users\\admin\\AppData\\Roaming\\npm\\node_modules\\needle');

var assetsJson = require('./assets.json');

const client = createClient({
    host: 'ip_change:port_change',
    secret: '0Z0czAihMerphYlVl6W3'
});

const job = assetsJson;

client.addJob(job).then(result => {

    result.on('created', (job) => {
    console.log('project has been created');
needle.post('domains/render/status', {'uid' : job.uid, 'state' : job.state});
})

result.on('started', (job) => {
    console.log('project rendering started');
needle.post('domains/render/status', {'uid' : job.uid, 'state' : job.state});
})

result.on('finished', (job) => {
    console.log('project rendering finished');
needle.post('domains/render/status', {'uid' : job.uid, 'state' : job.state});
})

result.on('error', (err) => {
    console.log('project rendering error');
needle.post('domains/render/status', {'uid' : err.uid, 'state' : err.state, 'error' : err.error });
})

}).catch(err => {
    console.log('job creation error:')
needle.post('domains/render/status', {'uid' : err.uid, 'state' : err.state, 'error' : err.stack });
})