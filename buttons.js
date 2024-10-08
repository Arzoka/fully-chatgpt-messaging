const directmessages = document.getElementById('direct-messages');
const hideButton = document.getElementById('hide-direct-messages');
const groupMessages = document.getElementById('group-messages');
const hideGroupButton = document.getElementById('hide-group-messages');

hideButton.addEventListener('click', () => {
	if (directmessages.style.display === 'none') {
		directmessages.style.display = 'block';
		hideButton.innerText = 'Hide';
	} else {
		directmessages.style.display = 'none';
		hideButton.innerText = 'Show';
	}
});

hideGroupButton.addEventListener('click', () => {
	if (groupMessages.style.display === 'none') {
		groupMessages.style.display = 'block';
		hideGroupButton.innerText = 'Hide';
	} else {
		groupMessages.style.display = 'none';
		hideGroupButton.innerText = 'Show';
	}
});
