const readInputFromPipedString = () => {
    try {
        return JSON.parse(require('fs').readFileSync(0, 'utf8'));
    } catch (e) {
        console.log('Error: invalid json input');
        process.exit(1);
    }
}

const outputToStdOut = (data) => console.log(JSON.stringify(data));


const main = () => {
    throw new Error('Unexpected Error!');
}
main();
