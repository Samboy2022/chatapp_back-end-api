// Network Configuration Test
// This script tests different IP configurations to find the working one

import axios from 'axios';

const testConfigurations = [
  { name: 'Localhost', ip: '127.0.0.1', port: '8000' },
  { name: 'Android Emulator', ip: '10.0.2.2', port: '8000' },
  { name: 'Network IP', ip: '192.168.55.83', port: '8000' },
];

async function testConfiguration(config) {
  const url = `http://${config.ip}:${config.port}/api/test`;
  console.log(`\n🔍 Testing ${config.name}: ${url}`);
  
  try {
    const response = await axios.get(url, {
      timeout: 5000,
      headers: {
        'Accept': 'application/json',
      }
    });
    
    console.log(`✅ ${config.name} - SUCCESS`);
    console.log(`   Status: ${response.status}`);
    console.log(`   Response: ${JSON.stringify(response.data)}`);
    return true;
  } catch (error) {
    console.log(`❌ ${config.name} - FAILED`);
    if (error.code) {
      console.log(`   Error Code: ${error.code}`);
    }
    if (error.response) {
      console.log(`   Status: ${error.response.status}`);
    } else {
      console.log(`   Error: ${error.message}`);
    }
    return false;
  }
}

async function runTests() {
  console.log('🚀 Starting Network Configuration Tests...\n');
  
  const results = [];
  
  for (const config of testConfigurations) {
    const success = await testConfiguration(config);
    results.push({ ...config, success });
  }
  
  console.log('\n📊 RESULTS SUMMARY:');
  console.log('==================');
  
  const workingConfigs = results.filter(r => r.success);
  
  if (workingConfigs.length > 0) {
    console.log('\n✅ WORKING CONFIGURATIONS:');
    workingConfigs.forEach(config => {
      console.log(`   ${config.name}: ${config.ip}:${config.port}`);
    });
    
    console.log('\n🔧 RECOMMENDED MOBILE APP CONFIG:');
    const recommended = workingConfigs[0];
    console.log(`   Update mobile_app/src/config/api.js:`);
    console.log(`   const LOCAL_IP = '${recommended.ip}';`);
    console.log(`   const API_PORT = '${recommended.port}';`);
  } else {
    console.log('\n❌ NO WORKING CONFIGURATIONS FOUND');
    console.log('   Please check if Laravel server is running:');
    console.log('   php artisan serve --host=0.0.0.0 --port=8000');
  }
}

runTests().catch(console.error);
