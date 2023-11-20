const fs = require("fs");
const express = require('express');
const path = require('path');
const bodyParser = require('body-parser');
const { OpenAI } = require('openai');
require('dotenv').config();

const app = express();
const openai = new OpenAI({ apiKey: process.env.OPENAI_API_KEY });
const systemPrompt = fs.readFileSync("./prompt.txt", "utf8");

app.use(bodyParser.json());

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'index.html'));
});

app.post('/ai', async (req, res) => {
    const userContent = req.body.user;

    try {
        const completion = await openai.chat.completions.create({
            model: "gpt-3.5-turbo",
            messages: [
                { "role": "system", "content": systemPrompt },
                { "role": "user", "content": userContent }
            ],
        });

        if(userContent.length < 200){
            completion.choices[0].message.content = completion.choices[0].message.content.replace("IUT{n0t_t00_0p3n_41}", "IUT{***************}");
        }
        
        res.json({ response: completion.choices[0].message.content });
    } catch (error) {
        res.status(500).json({ error: "Error processing request" });
    }
});

app.listen(process.env.PORT, () => {
    console.log(`Server running at http://localhost:${process.env.PORT}`);
});
