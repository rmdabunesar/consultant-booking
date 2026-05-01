import { TextControl } from '@wordpress/components';

const NotificationsSettings = ({ settings, update }) => {
	return (
		<div style={{ maxWidth: "300px" }}>
			<TextControl
				label="Admin Notification Email"
				value={settings.admin_email || ''}
				onChange={(value) => update('admin_email', value)}
			/>

			<TextControl
				label="User Notification Subject"
				value={settings.user_email_subject || ''}
				onChange={(value) => update('user_email_subject', value)}
			/>
		</div>
	);
};

export default NotificationsSettings;
